<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
     * $fillable definisce quali campi possono essere popolati tramite il "Mass Assignment".
     * - 'name': Il codice identificativo del permesso (es. "view_users").
     * - 'description': Una spiegazione leggibile (es. "Permette di vedere la lista utenti").
     */
    protected $fillable = ['name', 'description'];

    // Un permesso appartiene a molti ruoli
    public function roles()
    {
        /**
         * RELAZIONE: Many-to-Many (Molti-a-Molti)
         * * Un singolo permesso (es. "view_users") può essere assegnato a diversi ruoli 
         * (es. "Admin", "Manager", "Support").
         * * Questa funzione permette di risalire a tutti i ruoli che possiedono questo permesso.
         * Laravel si aspetta l'esistenza di una tabella pivot chiamata 'permission_role'.
         */
        return $this->belongsToMany(Role::class);
    }
}