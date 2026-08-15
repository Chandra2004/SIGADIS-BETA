<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StaffLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => ['required_without:phone_number', 'nullable', 'string'],
            'phone_number' => ['required_without:identifier', 'nullable', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
