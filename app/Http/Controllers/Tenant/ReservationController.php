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
                              
        return ReservationResource::collection($reservations);
    }

    public function store(StoreReservationRequest $request)
    {
        $reservation = Reservation::create($request->validated());
        $reservation->load(['customer', 'table']);
        return (new ReservationResource($reservation))->response()->setStatusCode(201);
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['customer', 'table']);
        return new ReservationResource($reservation);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $reservation->update($request->validated());
        $reservation->load(['customer', 'table']);
        return new ReservationResource($reservation);
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json(["message" => "Prenotazione eliminata"], 200);
    }
}
