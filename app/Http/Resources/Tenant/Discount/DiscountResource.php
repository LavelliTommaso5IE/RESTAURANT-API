<?php

namespace App\Http\Resources\Tenant\Discount;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
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
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'current_balance' => $this->current_balance,
            'min_order_value' => $this->min_order_value,
            'is_active' => $this->is_active,
            'valid_until' => $this->valid_until ? $this->valid_until->toDateTimeString() : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
