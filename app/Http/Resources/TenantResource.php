<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            ...$this->tenantInfo(),
            "domain" => $this->domainInfo(),
            "admin" => $this->adminInfo() // <-- AGGIUNTA QUI
        ];
    }

    private function tenantInfo(): array
    {
        return [
            "id" => $this->id,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "name" => $this->name,
            "description" => $this->description
        ];
    }

    private function domainInfo()
    {
        $domain = $this->domains->first();
        if ($domain) {
            return new DomainResource($domain);
        }
        return null;
    }

    // --- LA NUOVA FUNZIONE ---
    private function adminInfo()
    {
        // Controlliamo se nel controller abbiamo "appiccicato" l'utente al tenant
        if (isset($this->admin_user)) {
            return new UserResource($this->admin_user);
        }
        return null;
    }
}