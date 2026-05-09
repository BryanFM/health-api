<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('doctors.create');
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'unique:users,email'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'password'         => ['required', Password::min(8)->letters()->numbers()],
            'specialty_id'     => ['required', 'integer', 'exists:specialties,id'],
            'license_number'   => ['required', 'string', 'max:50', 'unique:doctors,license_number'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'bio'              => ['nullable', 'string', 'max:2000'],
            'is_available'     => ['nullable', 'boolean'],
        ];
    }
}
