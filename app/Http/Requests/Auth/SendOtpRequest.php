<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Nomor HP harus diawali 08 dan 10-15 digit.',
        ];
    }
}
