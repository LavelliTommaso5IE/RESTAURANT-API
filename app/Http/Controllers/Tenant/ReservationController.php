<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Reservation\StoreReservationRequest;
use App\Http\Requests\Tenant\Reservation\UpdateReservationRequest;
use App\Models\Reservation;
use App\Http\Resources\Tenant\Reservation\ReservationResource;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['customer', 'table']);

        if ($request->has('date')) {
            $query->whereDate('reservation_date', $request->query('date'));
        }

        $reservations = $query->orderBy('reservation_date', 'asc')
                              ->orderBy('reservation_time', 'asc')
                              ->get();
        return response()->json([
            'message' => 'Lista prenotazioni recuperata con successo',
            'data' => ReservationResource::collection($reservations)
        ], 200);
    }

    public function store(StoreReservationRequest $request)
    {
        $reservation = Reservation::create($request->validated());
        // TODO: Salvare uno snapshot dei dati del cliente e del tavolo al momento della prenotazione
        // in modo che lo storico mantenga i dati originali anche se cliente o tavolo vengono modificati.
        
        $reservation->load(['customer', 'table']);
        return response()->json([
            'message' => 'Prenotazione creata con successo',
            'data' => new ReservationResource($reservation)
        ], 201);
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['customer', 'table']);
        return response()->json([
            'message' => 'Dettaglio prenotazione recuperato con successo',
            'data' => new ReservationResource($reservation)
        ], 200);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $reservation->update($request->validated());
        $reservation->load(['customer', 'table']);
        return response()->json([
            'message' => 'Prenotazione aggiornata con successo',
            'data' => new ReservationResource($reservation)
        ], 200);
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json([
            "message" => "Prenotazione eliminata",
            "data" => null
        ], 200);
    }
}
