<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'unit'
    ];

    public function dishes()
    {
        return $this->belongsToMany(Dish::class)
                    ->withPivot('quantity', 'tolerance_percentage') //TODO: da chiarire
                    ->withTimestamps();
    }

    // TODO: Implementare avvisi per l'amministratore quando i prodotti stanno per finire (soglia di esaurimento)
}
