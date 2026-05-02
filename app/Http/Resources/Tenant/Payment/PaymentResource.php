<?php

namespace App\Http\Resources\Tenant\Payment;

use App\Http\Resources\Tenant\Discount\DiscountResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'discount' => new DiscountResource($this->whenLoaded('discount')),
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
