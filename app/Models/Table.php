<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'seats',
        'status',
        'pin',
        'parent_id'
    ];

    public function parent()
    {
        return $this->belongsTo(Table::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Table::class, 'parent_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
