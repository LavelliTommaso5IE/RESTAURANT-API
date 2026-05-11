<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Menu\StoreMenuRequest;
use App\Http\Requests\Tenant\Menu\UpdateMenuRequest;
use App\Models\Menu;
use App\Http\Resources\Tenant\Menu\MenuResource;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('dishes')->get();
        return response()->json([
            'message' => 'Lista menù recuperata con successo',
            'data' => MenuResource::collection($menus)
        ], 200);
    }

    public function store(StoreMenuRequest $request)
    {
        $menu = Menu::create($request->validated());

        if ($request->has('dishes')) {
            $menu->dishes()->sync($request->dishes);
        }

        $menu->load('dishes');

        return response()->json([
            'message' => 'Menù creato con successo',
            'data' => new MenuResource($menu)
        ], 201);
    }

    public function show(Menu $menu)
    {
        $menu->load('dishes');
        return response()->json([
            'message' => 'Dettaglio menù recuperato con successo',
            'data' => new MenuResource($menu)
        ], 200);
    }

    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        $menu->update($request->validated());

        if ($request->has('dishes')) {
            $menu->dishes()->sync($request->dishes);
        }

        $menu->load('dishes');

        return response()->json([
            'message' => 'Menù aggiornato con successo',
            'data' => new MenuResource($menu)
        ], 200);
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return response()->json([
            "message" => "Menù eliminato",
            "data" => null
        ], 200);
    }
}
