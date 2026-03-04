<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'nome',
        'cognome',
        'email',
        'password',
        'stato',
        'role_id' // Aggiungiamo la FK
    ];

    // Nascondiamo la password quando l'utente viene trasformato in Array/JSON!
    protected $hidden = [
        'password',
    ];

    // Un utente appartiene a UN ruolo
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}