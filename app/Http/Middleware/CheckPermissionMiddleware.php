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
        $userId = $request->attributes->get("user_id");
        
        // Carichiamo utente e relazioni
        $user = User::with('role.permissions')->find($userId);

        // CONTROLLO DI SICUREZZA: L'utente esiste? Ha un ruolo assegnato?
        if (!$user || !$user->role) {
            return response()->json([
                "message" => "Errore di configurazione: Ruolo mancante per questo utente"
            ], 403);
        }

        // ATTENZIONE AL PLURALE: permissions, non permission!
        $userPermissions = $user->role->permissions->pluck('name')->toArray();

        // La tua logica perfetta dell'array_intersect
        $matchingPermissions = array_intersect($userPermissions, $requiredPermissions);
        
        // Logica AND: li deve avere TUTTI
        if (count($matchingPermissions) !== count($requiredPermissions)) {
            return response()->json([
                "message" => "Utente non autorizzato: permessi insufficienti"
            ], 403); // <-- PUNTO E VIRGOLA AGGIUNTO QUI
        }

        return $next($request);
    }
}
