<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Puoi aggiungere logica di autorizzazione qui se necessario
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('id');
        
        return [
            'name' => 'sometimes|required|string|max:255',
            'surname' => 'sometimes|required|string|max:255',
            // Ignoro l'email dell'utente stesso se non l'ha cambiata, altrimenti unique:users esplode!
            'email' => 'sometimes|required|email|unique:users,email,' . $userId, 
            "password" => 'sometimes|required|string|min:8|confirmed',
            'role_id' => 'sometimes|required|exists:roles,id',
            'stato' => 'sometimes|required|string|in:attivo,temp,disattivato'
        ];
    }
}
