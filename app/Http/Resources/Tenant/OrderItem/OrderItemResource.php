<?php

namespace App\Http\Resources\Tenant\OrderItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'order_id' => $this->order_id,
            'dish_id' => $this->dish_id,
            'dish_name' => $this->dish->name ?? null,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => number_format($this->unit_price * $this->quantity, 2, '.', ''),
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
