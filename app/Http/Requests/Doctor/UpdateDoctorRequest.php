<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('doctors.update');
    }

    public function rules(): array
    {
        $doctor   = $this->route('doctor');
        $doctorId = $doctor ? $doctor->id : null;
        $userId   = $doctor ? $doctor->user_id : null;

        return [
            'name'             => ['sometimes', 'string', 'max:255'],
            'email'            => ['sometimes', 'email', "unique:users,email,{$userId}"],
            'phone'            => ['nullable', 'string', 'max:20'],
            'specialty_id'     => ['sometimes', 'integer', 'exists:specialties,id'],
            'license_number'   => ['sometimes', 'string', 'max:50', "unique:doctors,license_number,{$doctorId}"],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'bio'              => ['nullable', 'string', 'max:2000'],
            'is_available'     => ['nullable', 'boolean'],
        ];
    }
}
