<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Discount\StoreDiscountRequest;
use App\Http\Requests\Tenant\Discount\UpdateDiscountRequest;
use App\Models\Discount;
use App\Http\Resources\Tenant\Discount\DiscountResource;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::all();
        return DiscountResource::collection($discounts);
    }

    public function store(StoreDiscountRequest $request)
    {
        $data = $request->validated();
        
        // Se il codice non è fornito, lo generiamo noi in modo casuale
        if (empty($data['code'])) {
            $data['code'] = strtoupper(\Illuminate\Support\Str::random(12));
        }

        // Se è una gift card, inizializziamo il saldo corrente col valore nominale
        if ($data['type'] === 'gift_card') {
            $data['current_balance'] = $data['value'];
        }

        $discount = Discount::create($data);
        return (new DiscountResource($discount))->response()->setStatusCode(201);
    }

    public function show(Discount $discount)
    {
        return new DiscountResource($discount);
    }

    public function showByCode($code)
    {
        $discount = Discount::where('code', $code)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>', now());
            })
            ->firstOrFail();

        return new DiscountResource($discount);
    }

    public function update(UpdateDiscountRequest $request, Discount $discount)
    {
        $oldValue = $discount->value;
        $discount->update($request->validated());

        // Se è una gift card e il valore nominale è cambiato, aggiorniamo il saldo residuo in proporzione
        if ($discount->type === 'gift_card' && $request->has('value')) {
            $diff = $discount->value - $oldValue;
            // Il nuovo saldo è il vecchio saldo + la differenza, ma non può scendere sotto zero
            // e non dovrebbe mai superare il nuovo valore nominale (nel caso di ricariche o tagli)
            $newBalance = max(0, $discount->current_balance + $diff);
            
            $discount->update([
                'current_balance' => $newBalance
            ]);
        }

        return new DiscountResource($discount);
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return response()->json(['message' => 'Sconto eliminato (Soft Delete)'], 200);
    }
}
