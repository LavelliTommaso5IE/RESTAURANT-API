<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Payment\StorePaymentRequest;
use App\Http\Resources\Tenant\Payment\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Registra una tranche di pagamento
     */
    public function store(StorePaymentRequest $request, Order $order)
    {
        if ($order->status !== 'open') {
            return response()->json(['message' => 'L\'ordine è già chiuso.'], 422);
        }

        return DB::transaction(function () use ($request, $order) {
            
            // 1. Verifichiamo di non pagare più del dovuto
            $paidAmount = (float) $order->payments()->sum('amount');
            $remaining = round((float) $order->final_amount - $paidAmount, 2);

            if ($request->amount > ($remaining + 0.01)) {
                return response()->json([
                    'message' => 'L\'importo inserito supera il saldo rimanente dell\'ordine.',
                    'remaining' => $remaining
                ], 422);
            }

            $discountId = null;

            // 2. Se il pagamento è via Gift Card, cerchiamo il buono
            if ($request->payment_method === 'gift_card') {
                $discountCode = $request->input('discount_code');
                
                if (!$discountCode) {
                    return response()->json(['message' => 'Per il pagamento con Gift Card è necessario fornire il codice del buono.'], 422);
                }

                $discount = Discount::where('code', $discountCode)
                    ->where('type', 'gift_card')
                    ->where('is_active', true)
                    ->first();

                if (!$discount) {
                    return response()->json(['message' => 'Gift Card non valida o inesistente.'], 404);
                }

                // Verifica che la stessa Gift Card non sia già stata usata su questo ordine
                $alreadyUsed = $order->payments()
                    ->where('payment_method', 'gift_card')
                    ->where('discount_id', $discount->id)
                    ->exists();

                if ($alreadyUsed) {
                    return response()->json(['message' => 'Questa Gift Card è già stata utilizzata per questo ordine.'], 422);
                }

                if ($discount->current_balance < $request->amount) {
                    return response()->json([
                        'message' => 'Saldo Gift Card insufficiente.',
                        'available' => $discount->current_balance
                    ], 422);
                }

                // Scaliamo il saldo
                $discount->decrement('current_balance', $request->amount);
                $discount->refresh(); // Rilegge il valore aggiornato dal DB

                // Se il saldo è esaurito, disattiviamo la Gift Card
                if ($discount->current_balance <= 0) {
                    $discount->update([
                        'current_balance' => 0, // Evita valori negativi per arrotondamenti
                        'is_active' => false
                    ]);
                }
                $discountId = $discount->id;
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'discount_id' => $discountId,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes
            ]);

            return new PaymentResource($payment->load('discount'));
        });
    }

    /**
     * Elimina un pagamento (Storno)
     */
    public function destroy(Payment $payment)
    {
        $order = $payment->order;

        if ($order->status !== 'open') {
            return response()->json(['message' => 'Non puoi eliminare pagamenti di un ordine già chiuso.'], 422);
        }

        return DB::transaction(function () use ($payment, $order) {
            
            // Se il pagamento era via Gift Card, rimborsiamo lo specifico buono usato
            if ($payment->payment_method === 'gift_card' && $payment->discount_id) {
                $payment->discount->increment('current_balance', $payment->amount);
            }

            $payment->delete();

            return response()->json(['message' => 'Pagamento stornato con successo.'], 200);
        });
    }
}
