<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cover',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dishes()
    {
        return $this->belongsToMany(Dish::class)->withTimestamps();
    }

    // TODO: Aggiungere logica e campi per gli orari di validità del menù (es. solo pranzo, solo cena)
}
