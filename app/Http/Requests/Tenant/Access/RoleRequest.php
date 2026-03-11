<?php

namespace App\Http\Requests\Tenant\Access;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Se stiamo aggiornando, la rotta ha l'oggetto 'role'. Estraiamo il suo ID.
        // Se stiamo creando, 'role' non c'è, quindi sarà null.
        $roleId = $this->route('role') ? $this->route('role')->id : null;

        return [
            // Il nome deve essere unico nella tabella roles. 
            // Ignora il ruolo stesso se lo stiamo solo aggiornando!
            'name' => 'required|string|max:255|unique:roles,name,' . $roleId,

            'description' => 'required|string|max:1000'
        ];
    }
}
