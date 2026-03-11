<?php

namespace App\Http\Resources\Tenant\Access;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionRoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...$this->roleInfo(),
            "permissions" => $this->getPermissionsData()
        ];
    }

    private function roleInfo(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description
        ];
    }

    /**
     * Funzione privata per gestire la relazione in modo sicuro
     */
    private function getPermissionsData()
    {
        // Se il controller ha caricato la relazione 'permissions', la formatto
        if ($this->relationLoaded('permissions') && $this->permissions) {
            return PermissionResource::collection($this->permissions);
        }

        // Altrimenti non mostro nulla (oppure potresti restituire una stringa vuota o un array vuoto [])
        return null;
    }
}
