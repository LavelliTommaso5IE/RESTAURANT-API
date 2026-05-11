<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Category\StoreCategoryRequest;
use App\Http\Requests\Tenant\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Http\Resources\Tenant\Category\CategoryResource;
use App\Http\Resources\Tenant\Dish\DishResource;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(){
        $categories = Category::all();

        return response()->json([
            'message' => 'Lista categorie recuperata con successo',
            'data' => CategoryResource::collection($categories)
        ], 200);
    }

    public function show(Category $category){
        // Ottengo i piatti della categoria paginati (es. 10 per pagina)
        $dishes = $category->dishes()->paginate(10);

        // Ritorno i dettagli della categoria e i piatti paginati
        return response()->json([
            'message' => 'Dettaglio categoria e piatti recuperati',
            'data' => [
                'category' => new CategoryResource($category),
                'dishes' => DishResource::collection($dishes)->response()->getData(true)
            ]
        ], 200);
    }

    public function store(StoreCategoryRequest $request){
        $category = Category::create($request->validated());

        //restituisco l'oggetto appena creato
        return response()->json([
            'message' => 'Categoria creata con successo',
            'data' => new CategoryResource($category)
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category){
        $category->update($request->validated());

        //restituisco l'oggetto appena aggiornato
        return response()->json([
            'message' => 'Categoria aggiornata con successo',
            'data' => new CategoryResource($category)
        ], 200);
    }

    public function destroy(Category $category){
        $category->delete();

        //restituisco una risposta vuota con codice 204 (No Content)
        return response()->json([
            "message" => "Categoria eliminata",
            "data" => null
        ], 200);
    }
}
