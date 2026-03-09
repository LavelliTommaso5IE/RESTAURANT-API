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

            $userData = $request->validated();
            $userData["role_id"] = $userRole->id;

            // SALVO IL NUOVO UTENTE IN UNA VARIABILE
            $newUser = User::create($userData);
            $newUser->load("role");
            
        } catch (Exception $e) {
            return response()->json([ // AGGIUNTO IL RETURN
                "message" => "Impossibile creare l'utente",
                "error" => $e->getMessage() // Comodo in fase di test
            ], 500);
        }

        return response()->json([ // AGGIUNTO IL RETURN
            "message" => "Utente creato con successo",
            "user" => new UserResource($newUser) // PASSO IL NUOVO UTENTE
        ], 201);
    }

    public function updateUser(UpdateUserRequest $request, User $user)
    {
        try {
            $updateData = $request->validated();
            $user->update($updateData);
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

    public function deleteUser(Request $request, User $user)
    {
        try {
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