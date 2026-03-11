<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Validazione Tenant (Dati Azienda)
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',

            // Validazione Admin (Dati Utente Amministratore)
            'admin_name' => 'required|string|max:255',
            'admin_surname' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8|confirmed', // 'confirmed' richiede un campo admin_password_confirmation
        ];
    }
}
