<?php

namespace App\Http\Resources\Tenant\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'table_id' => $this->table_id,
            'customer_id' => $this->customer_id,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'discount_id' => $this->discount_id,
            'discount_amount' => $this->discount_amount,
            'final_amount' => $this->final_amount,
            'notes' => $this->notes,
            'table' => new \App\Http\Resources\Tenant\Table\TableResource($this->whenLoaded('table')),
            'customer' => new \App\Http\Resources\Tenant\Customer\CustomerResource($this->whenLoaded('customer')),
            'discount' => new \App\Http\Resources\Tenant\Discount\DiscountResource($this->whenLoaded('discount')),
            'items' => \App\Http\Resources\Tenant\OrderItem\OrderItemResource::collection($this->whenLoaded('items')),
            'payments' => \App\Http\Resources\Tenant\Payment\PaymentResource::collection($this->whenLoaded('payments')),
            'paid_amount' => round((float) $this->payments->sum('amount'), 2),
            'remaining_amount' => round(max(0, (float) $this->final_amount - (float) $this->payments->sum('amount')), 2),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
