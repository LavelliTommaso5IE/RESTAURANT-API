<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'current_balance',
        'min_order_value',
        'is_active',
        'valid_until'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_until' => 'datetime',
        'value' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'min_order_value' => 'decimal:2',
    ];
}
