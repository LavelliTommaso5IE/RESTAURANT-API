<?php

namespace App\Http\Resources\Tenant\Table;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'seats' => $this->seats,
            'status' => $this->status,
            'pin' => $this->pin,
            'parent' => new TableResource($this->whenLoaded('parent')),
            'children' => TableResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
