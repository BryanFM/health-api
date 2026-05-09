<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('patients.create');
    }

    public function rules(): array
    {
        return [
            'name'                    => ['required', 'string', 'max:255'],
            'email'                   => ['required', 'email', 'unique:users,email'],
            'phone'                   => ['nullable', 'string', 'max:20'],
            'password'                => ['required', Password::min(8)->letters()->numbers()],
            'date_of_birth'           => ['required', 'date', 'before:today'],
            'gender'                  => ['required', 'in:male,female,other'],
            'blood_type'              => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'address'                 => ['nullable', 'string', 'max:500'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'allergies'               => ['nullable', 'string', 'max:1000'],
        ];
    }
}
