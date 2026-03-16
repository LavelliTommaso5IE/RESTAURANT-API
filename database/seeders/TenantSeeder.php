<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DEFINIAMO TUTTI I PERMESSI IN UN ARRAY PULITO
        $permissionsData = [
            // Utenti
            ['name' => 'view_users', 'description' => 'Permette di vedere la lista degli utenti'],
            ['name' => 'edit_users', 'description' => 'Permette di creare, modificare ed eliminare utenti'],

            // Ruoli
            ['name' => 'view_roles', 'description' => 'Permette di vedere la lista dei ruoli'],
            ['name' => 'edit_roles', 'description' => 'Permette di creare, modificare ed eliminare ruoli'],

            //Permessi
            ['name' => 'view_permissions', 'description' => 'Permette di vedere la lista dei ruoli'],

            //Categorie
            ['name' => 'view_categories', 'description' => 'Permette di vedere la lista delle categorie'],
            ['name' => 'edit_categories', 'description' => 'Permette di creare, modificare ed eliminare categorie'],

            // Altro
            ['name' => 'view_reports', 'description' => 'Permette di vedere i report aziendali'],
        ];

        $adminPermissionIds = [];

        // Creiamo i permessi nel DB e salviamo i loro ID per l'Admin
        foreach ($permissionsData as $data) {
            $permission = Permission::create($data);
            $adminPermissionIds[] = $permission->id; // Raccogliamo tutti gli ID
        }

        // 2. CREIAMO I RUOLI
        $adminRole = Role::create([
            'name' => 'admin',
            'description' => 'Amministratore totale del tenant'
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'description' => 'Utente standard con permessi limitati'
        ]);

        // 3. AGGANCIAMO I PERMESSI AI RUOLI

        // L'admin prende TUTTI i permessi (l'array pieno di ID che abbiamo raccolto prima)
        $adminRole->permissions()->attach($adminPermissionIds);

        // Lo user normale prende solo i permessi di LETTURA
        $userPermissions = Permission::whereIn('name', ['view_users', 'view_reports', 'view_permissions'])->pluck('id');
        $userRole->permissions()->attach($userPermissions);
    }
}