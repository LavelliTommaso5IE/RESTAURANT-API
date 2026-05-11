<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Product\StoreProductRequest;
use App\Http\Requests\Tenant\Product\UpdateProductRequest;
use App\Models\Product;
use App\Http\Resources\Tenant\Product\ProductResource;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Lista prodotti recuperata',
            'data' => ProductResource::collection($products)
        ], 200);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());
        return response()->json([
            'message' => 'Prodotto creato con successo',
            'data' => new ProductResource($product)
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json([
            'message' => 'Dettagli prodotto recuperati',
            'data' => new ProductResource($product)
        ], 200);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        return response()->json([
            'message' => 'Prodotto aggiornato con successo',
            'data' => new ProductResource($product)
        ], 200);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([
            "message" => "Prodotto eliminato",
            "data" => null
        ], 200);
    }
}
