<?php

namespace App\Http\Requests\Tenant\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone,' . $this->route('customer')->id,
            'email' => 'nullable|email|max:255|unique:customers,email,' . $this->route('customer')->id,
            'vat_number' => 'nullable|string|max:50|unique:customers,vat_number,' . $this->route('customer')->id,
            'tax_code' => 'nullable|string|max:50|unique:customers,tax_code,' . $this->route('customer')->id,
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ];
    }
}
