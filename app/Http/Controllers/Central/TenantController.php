<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Http\Requests\Central\StoreTenantRequest;
use App\Http\Resources\Central\TenantResource;
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

            // --- 2. CREAZIONE TENANT ---
            // Qui scatta Stancl/Tenancy: 
            // Viene creato un record nel DB centrale e, contemporaneamente, 
            // viene creato un NUOVO Database fisico sul server.
            $tenant = Tenant::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            // --- 3. ASSEGNAZIONE DOMINIO ---
            // Collega l'URL generato prima al nuovo database creato.
            $tenant->domains()->create([
                'domain' => $dominioCompleto
            ]);

            // --- 4. CREIAMO L'UTENTE NEL TENANT ---
            // Il metodo run() "teletrasporta" Laravel dentro il database del tenant

            // Variabile temporanea per estrarre l'utente fuori dal database del tenant
            $adminCreato = null;

            $tenant->run(function () use ($request, &$adminCreato) { // <-- NOTA IL "&" davanti ad adminCreato
            // Inizializziamo il database del tenant con i dati di base (es. i permessi standard)
                $seeder = new \Database\Seeders\TenantSeeder();
                $seeder->run();
                
                // Troviamo il ruolo "admin" (creato dal seeder appena eseguito)
                $roleAdmin = Role::where('name', 'admin')->first();

                // Creiamo l'utente
                $user = User::create([
                    'name' => $request->admin_name,
                    'surname' => $request->admin_surname,
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

    public function checkTenant()
    {
        // Se arriviamo qui, il middleware InitializeTenancyByDomain ha già confermato che il tenant esiste
        $tenant = tenant(); // Recupera l'oggetto tenant corrente

        return response()->json([
            "messages" => "Tenant trovato",
            "data" => [
                "exists" => true,
                "tenant_name" => $tenant->name, // Utile per il frontend (es. titolo della pagina)
                "description" => $tenant->description
            ]
        ], 200);
    }
}
