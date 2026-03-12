<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Models\RefreshToken; // Ricorda: se l'hai spostato in Tenant/Access, aggiorna questo percorso!
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Exception;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next, $createToken = true)
    {
        $jwtSecret = env('JWT_SECRET');
        $token = $request->cookie('Authorization');

        // 1. Se non c'è l'access token, passiamo subito al piano B (Refresh)
        if (!$token) {
            // CORREZIONE BUG 2: Aggiunto $createToken
            return $this->refreshToken($request, $next, $jwtSecret, $createToken);
        }

        try {
            // 2. Proviamo a decodificare il token
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

            // Se è valido, salviamo l'ID utente per il controller
            $request->attributes->add(['user_id' => $decoded->sub]);

        } catch (ExpiredException $e) {
            // 3. Se è scaduto, passiamo al piano B (Refresh)
            return $this->refreshToken($request, $next, $jwtSecret, $createToken);

        } catch (Exception $e) {
            // 4. Se è MANOMESSO
            return response()->json(['messaggio' => 'Token non valido o manomesso. Fai il login.'], 401)
                ->withCookie(Cookie::forget('Authorization'))
                ->withCookie(Cookie::forget('Refresh'));
        }

        // 5. Se il token originale andava bene, proseguiamo la richiesta normalmente
        return $next($request);
    }

    /**
     * Logica di rinnovo del token (Piano B)
     */
    private function refreshToken(Request $request, Closure $next, $jwtSecret, $createToken)
    {
        $refreshTokenString = $request->cookie('Refresh');

        // Se manca il refresh token
        if (!$refreshTokenString) {
            return response()->json(['messaggio' => 'Sessione scaduta'], 401)
                ->withCookie(Cookie::forget('Authorization'))
                ->withCookie(Cookie::forget('Refresh'));
        }

        // Cerchiamo il refresh token nel DB
        $refreshToken = RefreshToken::with('user')->where('token', $refreshTokenString)->first();

        // Se non esiste o è scaduto
        if (!$refreshToken || $refreshToken->expires_at->isPast()) {
            return response()->json(['messaggio' => 'Sessione scaduta'], 401)
                ->withCookie(Cookie::forget('Authorization'))
                ->withCookie(Cookie::forget('Refresh'));
        }

        // CORREZIONE BUG 1: Identifichiamo l'utente PRIMA di chiamare il controller!
        $request->attributes->add(['user_id' => $refreshToken->user_id]);

        if ($createToken) {
            // Rigeneriamo il nuovo Access Token
            $payload = [
                'iss' => url('/'),
                'sub' => $refreshToken->user_id,
                'role' => $refreshToken->user->role_id,
                'iat' => time(),
                'exp' => time() + (15 * 60) // Nuovi 15 minuti
            ];

            $newAccessToken = JWT::encode($payload, $jwtSecret, 'HS256');

            // FONDAMENTALE: Eseguiamo il controller ORA (che sa chi è l'utente)
            $response = $next($request);

            // Attacchiamo il cookie alla risposta e la restituiamo
            return $response->withCookie(cookie('Authorization', $newAccessToken, 15, '/', null, false, true));
        }

        // CORREZIONE BUG 3: Se $createToken è false, mandiamo comunque avanti la richiesta
        return $next($request);
    }
}