<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'unique:healthcare_workers,phone_number'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['bidan', 'kader'])],
            'str_number' => ['required_if:role,bidan', 'nullable', 'string', 'max:50'],
            'appointment_letter_ref' => ['required_if:role,kader', 'nullable', 'string', 'max:255'],
            'region_code' => ['required', 'string', 'max:20'],
        ];
    }
}
