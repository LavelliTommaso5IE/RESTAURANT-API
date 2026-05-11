<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Customer\StoreCustomerRequest;
use App\Http\Requests\Tenant\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Http\Resources\Tenant\Customer\CustomerResource;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Lista clienti recuperata con successo',
            'data' => CustomerResource::collection($customers)
        ], 200);
    }

    public function store(StoreCustomerRequest $request)
    {
        return response()->json([
            'message' => 'Cliente creato con successo',
            'data' => new CustomerResource($customer)
        ], 201);
    }

    public function show(Customer $customer)
    {
        return response()->json([
            'message' => 'Dettaglio cliente recuperato con successo',
            'data' => new CustomerResource($customer)
        ], 200);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        return response()->json([
            'message' => 'Cliente aggiornato con successo',
            'data' => new CustomerResource($customer)
        ], 200);
    }

    public function destroy(Customer $customer)
    {
        return response()->json([
            "message" => "Cliente eliminato",
            "data" => null
        ], 200);
    }

    public function reservations(Request $request, Customer $customer)
    {
        $year = $request->query('year', now()->year);

        $reservations = $customer->reservations()
            ->with('table')
            ->whereYear('reservation_date', $year)
            ->orderBy('reservation_date', 'desc')
            ->orderBy('reservation_time', 'desc')
            ->paginate(15);

        return response()->json([
            'message' => 'Storico prenotazioni cliente',
            'data' => \App\Http\Resources\Tenant\Reservation\ReservationResource::collection($reservations)->response()->getData(true)
        ], 200);
    }
}
