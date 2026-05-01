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
        return TableResource::collection($tables);
    }

    public function store(StoreTableRequest $request)
    {
        $table = Table::create($request->validated());
        return (new TableResource($table))->response()->setStatusCode(201);
    }

    public function show(Table $table)
    {
        $table->load(['parent', 'children']);
        return new TableResource($table);
    }

    public function update(UpdateTableRequest $request, Table $table)
    {
        $table->update($request->validated());
        $table->load(['parent', 'children']);
        return new TableResource($table);
    }

    public function destroy(Table $table)
    {
        $table->delete();
        return response()->json(["message" => "Tavolo eliminato"], 200);
    }

    public function join(Request $request, Table $table)
    {
        $request->validate([
            'parent_id' => 'required|exists:tables,id|not_in:' . $table->id
        ]);

        $table->update(['parent_id' => $request->parent_id]);
        $table->load(['parent', 'children']);
        return new TableResource($table);
    }

    public function separate(Table $table)
    {
        $table->update(['parent_id' => null]);
        $table->load(['parent', 'children']);
        return new TableResource($table);
    }

    public function generatePin(Table $table)
    {
        if ($table->pin !== null) {
            return response()->json(['pin' => $table->pin]);
        }

        $pin = Str::random(64);
        $table->update(['pin' => $pin]);
        return response()->json(['pin' => $pin]);
    }

    public function clearTable(Table $table)
    {
        $newStatus = ($table->status === 'cleaning') ? 'free' : 'cleaning';

        $table->update([
            'pin' => null,
            'status' => $newStatus
        ]);
        $table->load(['parent', 'children']);
        return new TableResource($table);
    }
}
