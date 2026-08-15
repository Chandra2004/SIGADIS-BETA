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
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/', 'unique:healthcare_workers,phone_number'],
            'password' => ['required', 'string', 'min:8', function ($attribute, $value, $fail) {
                if ($this->has('password_confirmation') && $this->input('password_confirmation') !== $value) {
                    $fail('Konfirmasi kata sandi tidak cocok.');
                }
            }],
            'role' => ['required', Rule::in(['bidan', 'kader'])],
            'str_number' => ['required_if:role,bidan', 'nullable', 'string', 'max:50'],
            'appointment_letter_ref' => ['required_if:role,kader', 'nullable', 'string', 'max:255'],
            'region_code' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.unique' => 'Nomor handphone ini sudah terdaftar sebagai akun tenaga kesehatan.',
            'phone_number.regex' => 'Format nomor handphone tidak valid (contoh: 08123456789).',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'str_number.required_if' => 'Nomor STR wajib diisi untuk peran Bidan.',
            'appointment_letter_ref.required_if' => 'Nomor/Ref SK Desa wajib diisi untuk peran Kader.',
        ];
    }
}
