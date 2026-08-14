<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterPregnancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mother_name' => ['required', 'string', 'max:255'],
            'estimated_due_date' => ['nullable', 'date', 'required_if:hpl_is_estimated,false'],
            'hpl_is_estimated' => ['required', 'boolean'],
            'gestational_age_weeks_at_registration' => ['required', 'integer', 'min:0', 'max:45'],
            'is_twin_pregnancy' => ['required', 'boolean'],
            'has_prior_cesarean' => ['required', 'boolean'],
            'has_gestational_diabetes' => ['required', 'boolean'],
            'has_chronic_hypertension' => ['required', 'boolean'],
            'other_medical_conditions' => ['nullable', 'array'],
            'other_medical_conditions.*' => [Rule::in(['heart_disease', 'asthma', 'kidney_disorder', 'severe_anemia'])],
            'medical_notes' => ['nullable', 'string', 'max:1000'],
            'region_code' => ['required', 'string', 'max:20'],
            // Opsional -- bukan bagian data yang eksplisit disebut consent awal.
            'address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            // Defense-in-depth: consent tetap divalidasi di backend, bukan
            // hanya mengandalkan tombol disabled di frontend (Flows.md §3.1.4).
            'consent_granted' => ['required', 'accepted'],
            'consent_version' => ['required', 'string', 'max:20'],
            'selected_midwife_id' => [
                'nullable',
                Rule::exists('healthcare_workers', 'id')->where('role', 'bidan')->where('status', 'verified'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'consent_granted.accepted' => 'Registrasi tidak dapat dilanjutkan tanpa persetujuan (informed consent) aktif.',
        ];
    }
}
