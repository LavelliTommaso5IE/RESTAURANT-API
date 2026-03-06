<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash; // Aggiunto per le password

class UserController extends Controller
{
    public function createUser(CreateUserRequest $request)
    {
        $userId = $request->attributes->get("user_id");

        // Ottimizzazione: Prendo subito l'utente, il suo ruolo e i suoi permessi tutti insieme
        $user = User::with('role.permissions')->find($userId);

        $find = false;
        // Senza parentesi per accedere ai dati reali!
        foreach ($user->role->permissions as $permission) {
            // NOTA: controlla se il tuo permesso è al singolare o plurale (es. edit_users)
            if ($permission->name == "edit_users") {
                $find = true;
                break; // Se lo trovi, puoi fermare il ciclo per risparmiare tempo
            }
        }

        if (!$find) {
            return response()->json([ // AGGIUNTO IL RETURN
                "message" => "Impossibile eseguire l'azione: permessi insufficienti."
            ], 403);
        }

        try {
            // Prendiamo l'oggetto ruolo "user" per estrarne poi l'ID
            $userRole = Role::where("name", "=", "user")->first();

            $userData = [
                "nome" => $request->name,       // Controlla che nel DB non si chiami 'nome'
                "cognome" => $request->surname, // Controlla che nel DB non si chiami 'cognome'
                "email" => $request->email,
                "password" => Hash::make($request->password), // AGGIUNTO HASH
                "stato" => "temp",
                "role_id" => $userRole->id // Estraggo l'ID
            ];

            // SALVO IL NUOVO UTENTE IN UNA VARIABILE
            $newUser = User::create($userData);

            $userCreato = User::with("role")->find($newUser->id);
        } catch (Exception $e) {
            return response()->json([ // AGGIUNTO IL RETURN
                "message" => "Impossibile creare l'utente",
                "error" => $e->getMessage() // Comodo in fase di test
            ], 500);
        }

        return response()->json([ // AGGIUNTO IL RETURN
            "message" => "Utente creato con successo",
            "user" => new UserResource($userCreato) // PASSO IL NUOVO UTENTE
        ], 201);
    }
}