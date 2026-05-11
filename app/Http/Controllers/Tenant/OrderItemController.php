<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\OrderItem\UpdateOrderItemStatusRequest;
use App\Http\Resources\Tenant\OrderItem\OrderItemResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    /**
     * Aggiunge un piatto all'ordine (Comanda)
     */
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'dish_id' => 'required|exists:dishes,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        if ($order->status !== 'open') {
            return response()->json(['message' => 'Non puoi aggiungere piatti a un ordine chiuso.', 'data' => null], 422);
        }

        $dish = Dish::findOrFail($request->dish_id);

        if (!$dish->is_orderable) {
            return response()->json(['message' => "Il piatto '{$dish->name}' non è attualmente ordinabile.", 'data' => null], 422);
        }

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'dish_id' => $dish->id,
            'quantity' => $request->quantity,
            'unit_price' => $dish->price, // Snapshot del prezzo attuale
            'status' => 'pending',
            'notes' => $request->notes
        ]);

        // Aggiorniamo il totale dell'ordine in tempo reale
        $this->updateOrderTotal($order);

        return response()->json([
            'message' => 'Comanda aggiunta all\'ordine',
            'data' => new OrderItemResource($orderItem->load('dish'))
        ], 201);
    }

    /**
     * Aggiorna lo stato della comanda (Kitchen/Bar)
     */
    public function updateStatus(UpdateOrderItemStatusRequest $request, OrderItem $orderItem)
    {
        $orderItem->update(['status' => $request->status]);
        
        // TODO: Implementare il consumo dei prodotti nel magazzino quando il piatto entra in preparazione/è servito.
        // TODO: Inviare email all'amministratore se il consumo porta una scorta sotto la soglia critica.

        return response()->json([
            'message' => 'Stato comanda aggiornato',
            'data' => new OrderItemResource($orderItem)
        ], 200);
    }

    /**
     * Rimuove un piatto (solo se non è già in preparazione o servito?)
     * Per ora permettiamo la cancellazione semplice.
     */
    public function destroy(OrderItem $orderItem)
    {
        $order = $orderItem->order;
        $orderItem->delete();
        
        $this->updateOrderTotal($order);
        
        return response()->json([
            'message' => 'Piatto rimosso dall\'ordine',
            'data' => null
        ], 200);
    }

    private function updateOrderTotal(Order $order)
    {
        $total = $order->items()->sum(DB::raw('quantity * unit_price'));
        
        $order->update([
            'total_amount' => $total,
            // Ricalcoliamo il final_amount se c'è uno sconto già applicato
            'final_amount' => max(0, $total - $order->discount_amount)
        ]);
    }
}
