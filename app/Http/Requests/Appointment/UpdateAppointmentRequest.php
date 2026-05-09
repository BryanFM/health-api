<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('appointments.update');
    }

    public function rules(): array
    {
        return [
            'scheduled_at'     => ['sometimes', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'in:15,30,45,60'],
            'status'           => ['sometimes', 'in:pending,confirmed,completed,no_show'],
            'reason'           => ['sometimes', 'string', 'max:1000'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ];
    }
}
