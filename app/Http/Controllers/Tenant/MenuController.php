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
        return MenuResource::collection($menus);
    }

    public function store(StoreMenuRequest $request)
    {
        $menu = Menu::create($request->validated());

        if ($request->has('dishes')) {
            $menu->dishes()->sync($request->dishes);
        }

        $menu->load('dishes');

        return (new MenuResource($menu))->response()->setStatusCode(201);
    }

    public function show(Menu $menu)
    {
        $menu->load('dishes');
        return new MenuResource($menu);
    }

    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        $menu->update($request->validated());

        if ($request->has('dishes')) {
            $menu->dishes()->sync($request->dishes);
        }

        $menu->load('dishes');

        return new MenuResource($menu);
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return response()->json(["message" => "Menù eliminato"], 200);
    }
}
