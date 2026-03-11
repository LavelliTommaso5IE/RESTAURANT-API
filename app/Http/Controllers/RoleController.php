<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignPermissionRequest;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Resources\PermissionRoleResource;
use App\Http\Requests\RoleRequest;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Usiamo with() per caricare i permessi di TUTTI i ruoli con una singola query
        $roles = Role::with('permissions')->get();

        return response()->json([
            "message" => "Lista ruoli",
            "data" => PermissionRoleResource::collection($roles)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        $newRole = Role::create($request->validated());

        // 2. Carichiamo la relazione sul ruolo appena creato
        $newRole->load('permissions');

        return response()->json([
            "message" => "Ruolo creato con successo",
            "data" => new PermissionRoleResource($newRole)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        // 3. Carichiamo i permessi per questo singolo ruolo prima di mostrarlo
        $role->load('permissions');

        return response()->json([
            "message" => "Dettagli ruolo",
            "data" => new PermissionRoleResource($role)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, Role $role)
    {
        $role->update($request->validated());

        // 4. Carichiamo la relazione aggiornata prima di rispondere
        $role->load('permissions');

        return response()->json([
            "message" => "Ruolo aggiornato con successo",
            "data" => new PermissionRoleResource($role)
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            "message" => "Ruolo eliminato con successo"
        ], 200);
    }

    /**
     * Add permissions to a specific role
     */

    public function assignPermissions(AssignPermissionRequest $request, Role $role)
    {
        // Estraiamo SOLO l'array dei numeri dal validatore!
        $role->permissions()->sync($request->validated('permission_ids'));

        $role->load("permissions");

        return response()->json([
            "message" => "Permissions updated correctly",
            "data" => new PermissionRoleResource($role)
        ], 200);
    }
}