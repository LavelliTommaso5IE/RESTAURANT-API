<?php

namespace App\Http\Controllers\Tenant\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Access\CreateUserRequest;
use App\Http\Requests\Tenant\Access\UpdateUserRequest;
use App\Http\Resources\Tenant\Access\UserResource;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
// use Illuminate\Support\Facades\DB; // Aggiunto per le transazioni
use Illuminate\Support\Facades\Hash; // Aggiunto per le password

class UserController extends Controller
{
    public function index()
    {
        $users = User::get();
        // Eager Loading: carica la relazione 'role' per tutti gli utenti in un'unica query.
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
            /**
             * Logica di business: in questo sistema, ogni nuovo utente creato 
             * riceve automaticamente il ruolo predefinito "user".
             */
            $userRole = Role::where("name", "=", "user")->first();

            $userData = $request->validated();
            // Assegniamo forzatamente l'ID del ruolo "user" trovato sopra.
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
        /**
         * L'uso di User $user: è il Route Model Binding di Laravel.
         * Laravel cerca automaticamente l'utente nel DB usando l'ID passato nell'URL.
         */
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
