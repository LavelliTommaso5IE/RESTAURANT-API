<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Campi che possiamo riempire in massa (mass assignment)
    protected $fillable = ['name', 'description'];

    // Un ruolo ha molti utenti
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Un ruolo ha molti permessi (tabella pivot permission_role)
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}