<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string'],
            'otp_request_id' => ['required', 'string', 'uuid'],
            'otp_code' => ['required', 'string', 'size:6'],
        ];
    }
}
