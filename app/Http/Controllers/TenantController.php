<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Resources\TenantResource;
use App\Models\User;
use App\Models\Role;

class TenantController extends Controller
{
    /**
     * Metodo per creare un nuovo Tenant
     */
    public function store(StoreTenantRequest $request)
    {
        try {
            // 1. Logica dello slug e dominio
            $baseSlug = Str::slug($request->name);
            $dominioCompleto = $baseSlug . '.localhost';
            $contatore = 1;

            while (DB::table('domains')->where('domain', $dominioCompleto)->exists()) {
                $dominioCompleto = $baseSlug . '-' . $contatore . '.localhost';
                $contatore++;
            }

            // 2. Creiamo il Tenant (qui parte l'automatismo che crea il DB e le tabelle)
            $tenant = Tenant::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            // 3. Creiamo il Dominio associato
            $tenant->domains()->create([
                'domain' => $dominioCompleto
            ]);

            // --- LA NOVITÀ: CREIAMO L'UTENTE NEL TENANT ---
            // Il metodo run() "teletrasporta" Laravel dentro il database del tenant
            // Dentro TenantController.php

            // Variabile temporanea per estrarre l'utente fuori dal database del tenant
            $adminCreato = null;

            $tenant->run(function () use ($request, &$adminCreato) { // <-- NOTA IL "&" davanti ad adminCreato

                $seeder = new \Database\Seeders\TenantSeeder();
                $seeder->run();
                // ... Logica del seeder ...
                $roleAdmin = Role::where('name', 'admin')->first();

                // Creiamo l'utente
                $user = User::create([
                    'nome' => $request->admin_name,
                    'cognome' => $request->admin_surname,
                    'email' => $request->admin_email,
                    'password' => bcrypt($request->admin_password),
                    'stato' => 'attivo',
                    'role_id' => $roleAdmin->id
                ]);

                // Lo ricarichiamo con il suo ruolo usando "with('role')" e lo salviamo nella variabile esterna
                $adminCreato = User::with('role')->find($user->id);
            });

            // IL TRUCCO: Appiccichiamo l'utente appena creato all'oggetto Tenant!
            $tenant->admin_user = $adminCreato;
            $tenant->load('domains');

            return response()->json([
                "message" => "Tenant creato con successo!",
                "data" => new TenantResource($tenant) // Ora la resource troverà l'admin_user!
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'messaggio' => 'Errore durante la creazione del tenant.',
                'dettaglio_errore' => $e->getMessage()
            ], 500);
        }
    }
}