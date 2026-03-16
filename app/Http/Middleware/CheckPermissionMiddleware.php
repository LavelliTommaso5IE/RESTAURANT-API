<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$requiredPermissions)
    {
        // 1. RECUPERO IDENTITÀ
        // Prende l'ID utente che il JwtMiddleware ha salvato negli attributi della richiesta.
        // Senza il JwtMiddleware, questo valore sarebbe nullo.
        $userId = $request->attributes->get("user_id");
        
        // 2. CARICAMENTO DATI (Eager Loading)
        // Carica l'utente dal database del tenant, includendo il suo ruolo 
        // e tutti i permessi associati a quel ruolo in un'unica query.
        $user = User::with('role.permissions')->find($userId);

        // CONTROLLO DI SICUREZZA: L'utente esiste? Ha un ruolo assegnato?
        if (!$user || !$user->role) {
            return response()->json([
                "message" => "Errore di configurazione: Ruolo mancante per questo utente"
            ], 403);
        }

        // 4. ESTRAZIONE PERMESSI
        // Trasforma la collezione di oggetti Permission in un semplice array di stringhe (nomi).
        // Es: ['view_users', 'edit_users']
        $userPermissions = $user->role->permissions->pluck('name')->toArray();

        // 5. IL CONFRONTO (Logica Matematica)
        $matchingPermissions = array_intersect($userPermissions, $requiredPermissions);
        
        // Logica AND: li deve avere TUTTI
        if (count($matchingPermissions) !== count($requiredPermissions)) {
            return response()->json([
                "message" => "Utente non autorizzato: permessi insufficienti"
            ], 403); // <-- PUNTO E VIRGOLA AGGIUNTO QUI
        }

        // L'utente ha superato tutti i controlli, la richiesta può procedere al Controller.
        return $next($request);
    }
}
