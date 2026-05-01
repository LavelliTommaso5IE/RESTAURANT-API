<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Dish\StoreDishRequest;
use App\Http\Requests\Tenant\Dish\UpdateDishRequest;
use App\Models\Dish;
use App\Http\Resources\Tenant\Dish\DishResource;
use Illuminate\Http\Request;

class DishController extends Controller
{
    public function index()
    {
        $dishes = Dish::with('category')->get();

        return DishResource::collection($dishes);
    }

    public function store(StoreDishRequest $request)
    {
        $dish = Dish::create($request->validated());

        if ($request->has('products')) {
            $syncData = [];
            foreach ($request->products as $product) {
                $syncData[$product['id']] = [
                    'quantity' => $product['quantity'],
                    'tolerance_percentage' => $product['tolerance_percentage'] ?? 0
                ];
            }
            $dish->products()->sync($syncData);
        }

        // Load the category and products to include them in the response resource
        $dish->load(['category', 'products']);

        return (new DishResource($dish))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Dish $dish)
    {
        $dish->load(['category', 'products']);
        return new DishResource($dish);
    }

    public function update(UpdateDishRequest $request, Dish $dish)
    {
        $dish->update($request->validated());

        if ($request->has('products')) {
            $syncData = [];
            foreach ($request->products as $product) {
                $syncData[$product['id']] = [
                    'quantity' => $product['quantity'],
                    'tolerance_percentage' => $product['tolerance_percentage'] ?? 0
                ];
            }
            $dish->products()->sync($syncData);
        }

        // Load the category and products to include them in the response resource
        $dish->load(['category', 'products']);

        return new DishResource($dish);
    }

    public function destroy(Dish $dish)
    {
        $dish->delete();

        return response()->json(["message" => "Piatto eliminato"], 200);
    }
}
