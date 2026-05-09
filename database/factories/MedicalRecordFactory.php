<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    public function definition()
    {
        return [
            'patient_id'     => Patient::factory(),
            'doctor_id'      => Doctor::factory(),
            'appointment_id' => null,
            'diagnosis'      => $this->faker->sentence(),
            'treatment'      => $this->faker->paragraph(),
            'prescription'   => $this->faker->boolean(60) ? $this->faker->sentence() : null,
            'notes'          => $this->faker->boolean(40) ? $this->faker->paragraph() : null,
            'record_type'    => $this->faker->randomElement(['consultation', 'lab_result', 'imaging', 'follow_up', 'other']),
            'recorded_at'    => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
