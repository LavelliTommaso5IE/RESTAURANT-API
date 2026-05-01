<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Http\Resources\Tenant\Menu\MenuResource;
use Illuminate\Http\Request;

class PublicMenuController extends Controller
{
    public function index()
    {
        // Ritorna solo i menù attivi, caricando i piatti e le rispettive categorie
        $menus = Menu::where('is_active', true)
                     ->with(['dishes.category'])
                     ->get();
                     
        return MenuResource::collection($menus);
    }

    public function show(Menu $menu)
    {
        // Se il menù non è attivo, restituisce 404
        if (!$menu->is_active) {
            abort(404, 'Menù non trovato o non attivo.');
        }

        $menu->load(['dishes.category']);
        return new MenuResource($menu);
    }
}
