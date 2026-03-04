<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            ...$this->userInfo(),
            "role" => $this->roleInfo()
        ];
    }

    private function userInfo(): array
    {
        return [
            "id" => $this->id,
            "nome" => $this->nome,
            "cognome" => $this->cognome,
            "email" => $this->email,
            "stato" => $this->stato,
        ];
    }

    private function roleInfo()
    {
        // Se abbiamo caricato la relazione 'role', restituiamo la sua risorsa
        if ($this->relationLoaded('role') && $this->role) {
            return new RoleResource($this->role);
        }
        return null;
    }
}