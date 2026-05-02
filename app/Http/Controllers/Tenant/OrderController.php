<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Order\StoreOrderRequest;
use App\Http\Resources\Tenant\Order\OrderResource;
use App\Models\Order;
use App\Models\Table;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['table', 'customer', 'discount', 'items.dish', 'payments.discount'])->latest()->get();
        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $table = Table::findOrFail($request->table_id);
            
            // Verifica se il tavolo è già occupato
            if ($table->status === 'occupied') {
                return response()->json(['message' => 'Il tavolo è già occupato da un altro ordine attivo.'], 422);
            }

            $order = Order::create([
                'table_id' => $table->id,
                'status' => 'open',
                'total_amount' => 0,
                'final_amount' => 0,
                'notes' => $request->notes
            ]);

            // Aggiorna lo stato del tavolo
            $table->update(['status' => 'occupied']);

            return (new OrderResource($order->load('table')))->response()->setStatusCode(201);
        });
    }

    public function removeDiscount(Order $order)
    {
        if ($order->status !== 'open') {
            return response()->json(['message' => 'Non puoi modificare uno sconto su un ordine chiuso.'], 422);
        }

        // Se ci sono già dei pagamenti effettuati via Gift Card, non possiamo sganciarla senza prima stornare i pagamenti
        $hasGiftCardPayments = $order->payments()->where('payment_method', 'gift_card')->exists();
        if ($hasGiftCardPayments) {
            return response()->json(['message' => 'Impossibile rimuovere lo sconto: sono già stati registrati pagamenti via Gift Card. Elimina prima i pagamenti.'], 422);
        }

        $order->update([
            'discount_id' => null,
            'discount_amount' => 0,
            'final_amount' => $order->total_amount
        ]);

        return new OrderResource($order->load('discount'));
    }

    public function show(Order $order)
    {
        return new OrderResource($order->load(['table', 'customer', 'discount', 'items.dish', 'payments.discount']));
    }

    /**
     * Associa un cliente all'ordine (ricevuta nominativa)
     */
    public function associateCustomer(Request $request, Order $order)
    {
        $request->validate(['customer_id' => 'required|exists:customers,id']);
        
        $order->update(['customer_id' => $request->customer_id]);
        
        return new OrderResource($order->load('customer'));
    }

    /**
     * Applica uno sconto e calcola il totale finale
     */
    public function applyDiscount(Request $request, Order $order)
    {
        $request->validate([
            'discount_id' => 'required_without:discount_code|exists:discounts,id',
            'discount_code' => 'required_without:discount_id|string',
        ]);
        
        $discount = null;

        if ($request->has('discount_id')) {
            $discount = Discount::findOrFail($request->discount_id);
        } else {
            // Cerca per codice, assicurandosi che sia attivo e non scaduto
            $discount = Discount::where('code', $request->discount_code)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('valid_until')
                          ->orWhere('valid_until', '>', now());
                })
                ->first();

            if (!$discount) {
                return response()->json(['message' => 'Codice sconto non valido, scaduto o inesistente.'], 404);
            }
        }

        // Controllo limite di utilizzo
        if ($discount->usage_limit !== null && $discount->usage_count >= $discount->usage_limit) {
            return response()->json(['message' => 'Questo sconto ha raggiunto il limite massimo di utilizzi.'], 422);
        }

        // Le Gift Card ora si usano come metodo di pagamento, non come sconto globale
        if ($discount->type === 'gift_card') {
            return response()->json(['message' => 'Le Gift Card devono essere usate come metodo di pagamento, non come sconto globale.'], 422);
        }
        
        // Calcoliamo il totale attuale basato sugli items
        $totalAmount = $order->items()->sum(DB::raw('quantity * unit_price'));
        
        $discountAmount = 0;
        if ($discount->type === 'percentage') {
            $discountAmount = ($totalAmount * $discount->value) / 100;
        } elseif ($discount->type === 'fixed') {
            $discountAmount = $discount->value;
        } 
        // Nota: Per le 'gift_card' non impostiamo un discount_amount fisso qui, 
        // perché il cliente sceglierà quanto scalare durante il pagamento.

        $order->update([
            'total_amount' => $totalAmount,
            'discount_id' => $discount->id,
            'discount_amount' => $discountAmount,
            'final_amount' => max(0, $totalAmount - $discountAmount)
        ]);

        return new OrderResource($order->load('discount'));
    }

    /**
     * Chiude l'ordine e libera il tavolo
     */
    public function close(Order $order)
    {
        if ($order->status !== 'open') {
            return response()->json(['message' => 'L\'ordine è già chiuso o annullato.'], 422);
        }

        // Verifica che il pagamento sia completo
        $paidAmount = (float) $order->payments()->sum('amount');
        $finalAmount = round((float) $order->final_amount, 2);

        if ($paidAmount < $finalAmount) {
            $missing = round($finalAmount - $paidAmount, 2);
            return response()->json([
                'message' => 'Il totale pagato non copre il conto finale.',
                'missing' => $missing
            ], 422);
        }

        return DB::transaction(function () use ($order) {
            $order->update(['status' => 'closed']);
            
            // Incrementa il contatore dello sconto se presente
            if ($order->discount_id) {
                $order->discount()->increment('usage_count');
            }

            // Imposta il tavolo in stato "cleaning" (come da logica precedente dell'utente)
            $order->table->update(['status' => 'cleaning']);

            return new OrderResource($order);
        });
    }
}
