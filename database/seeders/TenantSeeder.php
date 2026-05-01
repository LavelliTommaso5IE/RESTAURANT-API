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
            // Utenti, Ruoli, Permessi
            ['name' => 'view_users', 'description' => 'Permette di vedere la lista degli utenti'],
            ['name' => 'edit_users', 'description' => 'Permette di creare, modificare ed eliminare utenti'],
            ['name' => 'view_roles', 'description' => 'Permette di vedere la lista dei ruoli'],
            ['name' => 'edit_roles', 'description' => 'Permette di creare, modificare ed eliminare ruoli'],
            ['name' => 'view_permissions', 'description' => 'Permette di vedere la lista dei permessi'],
            
            // Menu, Categorie, Piatti, Prodotti
            ['name' => 'view_menus', 'description' => 'Permette di vedere i menù'],
            ['name' => 'edit_menus', 'description' => 'Permette di gestire i menù'],
            ['name' => 'view_categories', 'description' => 'Permette di vedere la lista delle categorie'],
            ['name' => 'edit_categories', 'description' => 'Permette di gestire le categorie'],
            ['name' => 'view_dishes', 'description' => 'Permette di vedere la lista dei piatti'],
            ['name' => 'edit_dishes', 'description' => 'Permette di gestire i piatti'],
            ['name' => 'view_products', 'description' => 'Permette di vedere i prodotti/ingredienti'],
            ['name' => 'edit_products', 'description' => 'Permette di gestire i prodotti/ingredienti'],

            // Tavoli, Prenotazioni
            ['name' => 'view_tables', 'description' => 'Permette di vedere i tavoli'],
            ['name' => 'edit_tables', 'description' => 'Permette di gestire i tavoli'],
            ['name' => 'view_reservations', 'description' => 'Permette di vedere le prenotazioni'],
            ['name' => 'edit_reservations', 'description' => 'Permette di gestire le prenotazioni'],

            // Ordini, Comande
            ['name' => 'view_orders', 'description' => 'Permette di vedere gli ordini'],
            ['name' => 'edit_orders', 'description' => 'Permette di gestire gli ordini'],
            ['name' => 'view_comande', 'description' => 'Permette di vedere le comande'],
            ['name' => 'edit_comande', 'description' => 'Permette di gestire le comande'],

            // Clienti, Sconti, Reports, Logs
            ['name' => 'view_customers', 'description' => 'Permette di vedere i clienti'],
            ['name' => 'edit_customers', 'description' => 'Permette di gestire i clienti'],
            ['name' => 'view_discounts', 'description' => 'Permette di vedere gli sconti'],
            ['name' => 'edit_discounts', 'description' => 'Permette di gestire gli sconti'],
            ['name' => 'view_reports', 'description' => 'Permette di vedere i report aziendali'],
            ['name' => 'view_logs', 'description' => 'Permette di vedere i log di sistema'],
        ];

        $adminPermissionIds = [];

        // Creiamo i permessi nel DB e salviamo i loro ID per l'Admin
        foreach ($permissionsData as $data) {
            // Uso firstOrCreate per non duplicare i permessi se il seeder viene lanciato più volte
            $permission = Permission::firstOrCreate(['name' => $data['name']], $data);
            $adminPermissionIds[] = $permission->id;
        }

        // 2. CREIAMO I RUOLI (usando firstOrCreate per idempotenza)
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'description' => 'Amministratore totale del tenant'
        ]);

        $waiterRole = Role::firstOrCreate(['name' => 'cameriere'], [
            'description' => 'Cameriere addetto a tavoli, ordini e comande'
        ]);

        $chefRole = Role::firstOrCreate(['name' => 'cuoco'], [
            'description' => 'Cuoco addetto alla gestione delle comande e prodotti'
        ]);

        $cashierRole = Role::firstOrCreate(['name' => 'cassiere'], [
            'description' => 'Cassiere addetto ai pagamenti, sconti e report'
        ]);

        // 3. AGGANCIAMO I PERMESSI AI RUOLI (usando sync invece di attach per pulizia)
        
        // Admin prende TUTTI i permessi
        $adminRole->permissions()->sync($adminPermissionIds);

        // Cameriere
        $waiterPermissions = Permission::whereIn('name', [
            'view_menus', 'view_categories', 'view_dishes',
            'view_tables', 'edit_tables',
            'view_orders', 'edit_orders',
            'view_comande', 'edit_comande',
            'view_reservations', 'edit_reservations'
        ])->pluck('id');
        $waiterRole->permissions()->sync($waiterPermissions);

        // Cuoco
        $chefPermissions = Permission::whereIn('name', [
            'view_menus', 'view_categories', 'view_dishes',
            'view_products', 'edit_products',
            'view_comande', 'edit_comande'
        ])->pluck('id');
        $chefRole->permissions()->sync($chefPermissions);

        // Cassiere
        $cashierPermissions = Permission::whereIn('name', [
            'view_tables',
            'view_orders', 'edit_orders',
            'view_customers', 'edit_customers',
            'view_discounts', 'edit_discounts',
            'view_reports'
        ])->pluck('id');
        $cashierRole->permissions()->sync($cashierPermissions);
    }
}