<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('appointments.create');
    }

    public function rules(): array
    {
        return [
            'patient_id'       => ['required', 'integer', 'exists:patients,id'],
            'doctor_id'        => ['required', 'integer', 'exists:doctors,id'],
            'scheduled_at'     => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'in:15,30,45,60'],
            'reason'           => ['required', 'string', 'max:1000'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ];
    }
}
