<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('patients.update');
    }

    public function rules(): array
    {
        $patient = $this->route('patient');
        $userId  = $patient ? $patient->user_id : null;

        return [
            'name'                    => ['sometimes', 'string', 'max:255'],
            'email'                   => ['sometimes', 'email', "unique:users,email,{$userId}"],
            'phone'                   => ['nullable', 'string', 'max:20'],
            'date_of_birth'           => ['sometimes', 'date', 'before:today'],
            'gender'                  => ['sometimes', 'in:male,female,other'],
            'blood_type'              => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'address'                 => ['nullable', 'string', 'max:500'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'allergies'               => ['nullable', 'string', 'max:1000'],
        ];
    }
}
