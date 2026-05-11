<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Table\StoreTableRequest;
use App\Http\Requests\Tenant\Table\UpdateTableRequest;
use App\Models\Table;
use App\Http\Resources\Tenant\Table\TableResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::with(['parent', 'children'])->get();
        return response()->json([
            'message' => 'Lista tavoli recuperata',
            'data' => TableResource::collection($tables)
        ], 200);
    }

    public function store(StoreTableRequest $request)
    {
        $table = Table::create($request->validated());
        return response()->json([
            'message' => 'Tavolo creato con successo',
            'data' => new TableResource($table)
        ], 201);
    }

    public function show(Table $table)
    {
        $table->load(['parent', 'children']);
        return response()->json([
            'message' => 'Dettagli tavolo recuperati',
            'data' => new TableResource($table)
        ], 200);
    }

    public function update(UpdateTableRequest $request, Table $table)
    {
        $table->update($request->validated());
        $table->load(['parent', 'children']);
        return response()->json([
            'message' => 'Tavolo aggiornato con successo',
            'data' => new TableResource($table)
        ], 200);
    }

    public function destroy(Table $table)
    {
        $table->delete();
        return response()->json([
            "message" => "Tavolo eliminato",
            "data" => null
        ], 200);
    }

    public function join(Request $request, Table $table)
    {
        $request->validate([
            'parent_id' => 'required|exists:tables,id|not_in:' . $table->id
        ]);

        $table->update(['parent_id' => $request->parent_id]);
        $table->load(['parent', 'children']);
        return response()->json([
            'message' => 'Tavolo unito con successo',
            'data' => new TableResource($table)
        ], 200);
    }

    public function separate(Table $table)
    {
        $table->update(['parent_id' => null]);
        $table->load(['parent', 'children']);
        return response()->json([
            'message' => 'Tavolo separato con successo',
            'data' => new TableResource($table)
        ], 200);
    }

    public function generatePin(Table $table)
    {
        if ($table->pin !== null) {
            return response()->json([
                'message' => 'PIN già generato',
                'data' => ['pin' => $table->pin]
            ], 200);
        }

        $pin = Str::random(64);
        $table->update(['pin' => $pin]);
        return response()->json([
            'message' => 'PIN generato con successo',
            'data' => ['pin' => $pin]
        ], 200);
    }

    public function clearTable(Table $table)
    {
        $newStatus = ($table->status === 'cleaning') ? 'free' : 'cleaning';

        $table->update([
            'pin' => null,
            'status' => $newStatus
        ]);
        $table->load(['parent', 'children']);
        return response()->json([
            'message' => 'Stato tavolo pulizia aggiornato',
            'data' => new TableResource($table)
        ], 200);
    }
}
