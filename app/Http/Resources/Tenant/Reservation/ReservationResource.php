<?php

namespace App\Http\Resources\Tenant\Reservation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
            'reservation_date' => $this->reservation_date,
            'reservation_time' => date('H:i', strtotime($this->reservation_time)),
            'people_count' => $this->people_count,
            'notes' => $this->notes,
            'customer' => new \App\Http\Resources\Tenant\Customer\CustomerResource($this->whenLoaded('customer')),
            'table' => $this->whenLoaded('table', function () {
                return [
                    'id' => $this->table->id,
                    'name' => $this->table->name,
                    'seats' => $this->table->seats,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
