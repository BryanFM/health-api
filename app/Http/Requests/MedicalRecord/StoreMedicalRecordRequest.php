<?php

namespace App\Http\Requests\MedicalRecord;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('medical-records.create');
    }

    public function rules(): array
    {
        return [
            'patient_id'     => ['required', 'integer', 'exists:patients,id'],
            'doctor_id'      => ['required', 'integer', 'exists:doctors,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'diagnosis'      => ['required', 'string', 'max:2000'],
            'treatment'      => ['required', 'string', 'max:2000'],
            'prescription'   => ['nullable', 'string', 'max:2000'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'record_type'    => ['sometimes', 'in:consultation,lab_result,imaging,surgery,follow_up,other'],
            'recorded_at'    => ['nullable', 'date', 'before_or_equal:now'],
        ];
    }
}
