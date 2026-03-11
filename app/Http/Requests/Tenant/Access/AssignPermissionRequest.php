<?php

namespace App\Http\Requests\Tenant\Access;

use Illuminate\Foundation\Http\FormRequest;

class AssignPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "permission_ids" => "present|array",
            // Corretto: exists:[tabella],[colonna]
            "permission_ids.*" => "exists:permissions,id"
        ];
    }
}
