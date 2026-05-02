<?php

namespace App\Http\Requests\Tenant\Discount;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscountRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50|unique:discounts,code,' . $this->route('discount')->id,
            'type' => 'sometimes|required|in:percentage,fixed,gift_card',
            'value' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $type = $this->type ?? $this->route('discount')->type;
                    if ($type === 'percentage' && $value > 100) {
                        $fail('Lo sconto percentuale non può superare il 100%.');
                    }
                },
            ],
            'min_order_value' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'valid_until' => 'nullable|date',
        ];
    }
}
