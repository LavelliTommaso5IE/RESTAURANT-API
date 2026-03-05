<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Models\RefreshToken;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Exception;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $jwtSecret = env('JWT_SECRET');
        $token = $request->cookie('Authorization');

        // 1. Se non c'è l'access token, passiamo subito al piano B (Refresh)
        if (!$token) {
            return $this->refreshToken($request, $next, $jwtSecret);
        }

        try {
            // 2. Proviamo a decodificare il token
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

            // Se è valido, salviamo l'ID utente per il controller
            $request->attributes->add(['user_id' => $decoded->sub]);

        } catch (ExpiredException $e) {
            // 3. Se è scaduto, passiamo al piano B (Refresh)
            return $this->refreshToken($request, $next, $jwtSecret);

        } catch (Exception $e) {
            // 4. Se è MANOMESSO, cacciamo l'utente e forziamo la pulizia dei cookie DIRETTAMENTE sulla risposta
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
    private function refreshToken(Request $request, Closure $next, $jwtSecret)
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

        // BINGO! Rigeneriamo il nuovo Access Token
        $payload = [
            'iss' => url('/'),
            'sub' => $refreshToken->user_id,
            'role' => $refreshToken->user->role_id,
            'iat' => time(),
            'exp' => time() + (15 * 60) // Nuovi 15 minuti
        ];

        $newAccessToken = JWT::encode($payload, $jwtSecret, 'HS256');

        // Identifichiamo l'utente per questa richiesta
        $request->attributes->add(['user_id' => $refreshToken->user_id]);

        // FONDAMENTALE: Eseguiamo il controller e CATTURIAMO la risposta (es. il JSON di /api/me)
        $response = $next($request);

        // Ora attacchiamo il cookie DIRETTAMENTE alla risposta appena creata dal controller!
        return $response->withCookie(cookie('Authorization', $newAccessToken, 15, '/', null, false, true));
    }
}