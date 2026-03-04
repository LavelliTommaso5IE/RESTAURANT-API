<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "dominio_generato" => $this->domain,
            "link_da_visitare" => $this->generateFullUrl()
        ];
    }

    private function generateFullUrl(): string
    {
        // Prendiamo il dominio e aggiungiamo http:// davanti e :8000/ alla fine
        return 'http://' . $this->domain . ':8000/';
    }
}
