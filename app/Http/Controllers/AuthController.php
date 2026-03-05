<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validiamo i dati in ingresso
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. Cerchiamo l'utente nel database DEL TENANT (grazie a stancl/tenancy siamo già nel DB giusto!)
        $user = User::with("role")->where('email', $request->email)->first();

        // 3. Controlliamo se esiste e se la password è corretta usando Hash::check
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['messaggio' => 'Credenziali non valide'], 401);
        }

        // 4. CREAZIONE ACCESS TOKEN (JWT - Scade in 15 minuti)
        $jwtSecret = env('JWT_SECRET', 'chiave_di_riserva_cambiami');
        $payload = [
            'iss' => url('/'), // Chi emette il token
            'sub' => $user->id, // Soggetto (ID dell'utente)
            'role' => $user->role_id, // Un'info utile da avere nel token
            'iat' => time(), // Emesso adesso
            'exp' => time() + (15 * 60) // Scade tra 15 minuti
        ];

        $accessToken = JWT::encode($payload, $jwtSecret, 'HS256');

        // 5. CREAZIONE REFRESH TOKEN (Salvato su DB - Scade in 7 giorni)
        $refreshTokenString = Str::random(60); // Genera una stringa casuale di 60 caratteri sicura

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $refreshTokenString,
            'expires_at' => Carbon::now()->addDays(7)
        ]);

        // 6. CREIAMO I COOKIE HTTP-ONLY (Sicurezza massima contro attacchi XSS)
        // cookie($nome, $valore, $minuti)
        $cookieAccess = cookie('Authorization', $accessToken, 15, '/', null, false, true);
        $cookieRefresh = cookie('Refresh', $refreshTokenString, 60 * 24 * 7, '/', null, false, true);

        // 7. RESTITUIAMO LA RISPOSTA CON I COOKIE ATTACCATI
        return response()->json([
            'messaggio' => 'Login effettuato con successo!',
            // Opzionale: restituiamo anche i dati dell'utente, ma non i token (quelli sono al sicuro nei cookie)
            'user' => new UserResource($user)
        ])->withCookie($cookieAccess)->withCookie($cookieRefresh);
    }

    public function me(Request $request)
    {
        // 1. Recuperiamo l'ID dell'utente che il middleware ha estratto dal token
        $userId = $request->attributes->get('user_id');

        // 2. Cerchiamo l'utente nel DB del tenant (caricando anche il ruolo come abbiamo imparato!)
        $user = User::with('role')->find($userId);

        if (!$user) {
            return response()->json(['messaggio' => 'Utente non trovato'], 404);
        }

        // 3. Restituiamo i dati puliti usando la tua splendida Resource
        return response()->json([
            'user' => new UserResource($user)
        ]);
    }
}