<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CREIAMO I PERMESSI BASE
        $permessoScrittura = Permission::create([
            'name' => 'edit_users',
            'description' => 'Permette di modificare gli utenti'
        ]);

        $permessoLettura = Permission::create([
            'name' => 'view_reports',
            'description' => 'Permette di vedere i report'
        ]);

        // 2. CREIAMO I RUOLI
        $adminRole = Role::create([
            'name' => 'admin',
            'description' => 'Amministratore totale del tenant'
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'description' => 'Utente standard con permessi limitati'
        ]);

        // 3. AGGANCIAMO I PERMESSI AI RUOLI (Tabella Pivot)
        // L'admin prende tutto, lo user solo lettura
        $adminRole->permissions()->attach([$permessoScrittura->id, $permessoLettura->id]);
        $userRole->permissions()->attach([$permessoLettura->id]);
    }
}