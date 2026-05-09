<?php

namespace App\Http\Requests\Specialty;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('specialties.update');
    }

    public function rules(): array
    {
        $specialty   = $this->route('specialty');
        $specialtyId = $specialty ? $specialty->id : null;

        return [
            'name'        => ['sometimes', 'string', 'max:100', "unique:specialties,name,{$specialtyId}"],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }
}
