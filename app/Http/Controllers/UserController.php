<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash; // Aggiunto per le password

class UserController extends Controller
{
    public function index()
    {
        $users = User::get();
        $users->load("role");

        return response()->json([
            "message" => "Lista utenti",
            "data" => UserResource::collection($users)
        ], 200);
    }

    public function createUser(CreateUserRequest $request)
    {
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

            $userCreato = User::find($newUser->id);
            $userCreato->load("role");
            
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

    public function updateUser(UpdateUserRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Se l'email è presente nella richiesta, la valido e la aggiorno
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            IF($request->has('password')) {
                $user->password = Hash::make($request->password); // AGGIUNTO HASH
            }
            // Aggiorno gli altri campi se presenti
            if ($request->has('name')) {
                $user->nome = $request->name; // Controlla che nel DB non si chiami 'nome'
            }
            if ($request->has('surname')) {
                $user->cognome = $request->surname; // Controlla che nel DB non si chiami 'cognome'
            }
            if ($request->has('role_id')) {
                $user->role_id = $request->role_id;
            }
            if ($request->has('stato')) {
                $user->stato = $request->stato;
            }

            $user->save();

        } catch (Exception $e) {
            return response()->json([
                "message" => "Impossibile aggiornare l'utente",
                "error" => $e->getMessage()
            ], 500);
        }

        $user->load("role");

        return response()->json([
            "message" => "Utente aggiornato con successo",
            "user" => new UserResource($user)
        ], 200);
    }

    public function deleteUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
        } catch (Exception $e) {
            return response()->json([
                "message" => "Impossibile eliminare l'utente",
                "error" => $e->getMessage()
            ], 500);
        }

        return response()->json([
            "message" => "Utente eliminato con successo"
        ], 200);
    }
}