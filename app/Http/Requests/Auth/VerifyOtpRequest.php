<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone_number')) {
            $phone = preg_replace('/\D+/', '', (string) $this->phone_number);
            if (str_starts_with($phone, '62')) {
                $phone = '0' . substr($phone, 2);
            } elseif (str_starts_with($phone, '8')) {
                $phone = '0' . $phone;
            }
            $this->merge(['phone_number' => $phone]);
        }
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
