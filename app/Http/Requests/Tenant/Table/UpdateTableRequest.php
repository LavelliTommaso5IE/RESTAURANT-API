<?php

namespace App\Http\Requests\Tenant\Table;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTableRequest extends FormRequest
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
        return [
            'name' => 'sometimes|required|string|max:255|unique:tables,name,' . $this->route('table')->id,
            'seats' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|in:free,occupied,reserved,cleaning'
        ];
    }
}
