<?php

namespace App\Http\Requests\MedicalRecord;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('medical-records.update');
    }

    public function rules(): array
    {
        return [
            'diagnosis'    => ['sometimes', 'string', 'max:2000'],
            'treatment'    => ['sometimes', 'string', 'max:2000'],
            'prescription' => ['nullable', 'string', 'max:2000'],
            'notes'        => ['nullable', 'string', 'max:2000'],
            'record_type'  => ['sometimes', 'in:consultation,lab_result,imaging,surgery,follow_up,other'],
            'recorded_at'  => ['nullable', 'date', 'before_or_equal:now'],
        ];
    }
}
