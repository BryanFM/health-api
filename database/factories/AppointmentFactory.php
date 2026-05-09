<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        return [
            'patient_id'          => Patient::factory(),
            'doctor_id'           => Doctor::factory(),
            'scheduled_at'        => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'duration_minutes'    => $this->faker->randomElement([15, 30, 45, 60]),
            'status'              => $this->faker->randomElement(['pending', 'confirmed', 'completed']),
            'reason'              => $this->faker->sentence(),
            'notes'               => $this->faker->boolean(40) ? $this->faker->paragraph() : null,
            'cancellation_reason' => null,
        ];
    }

    public function pending()
    {
        return $this->state(['status' => 'pending']);
    }

    public function confirmed()
    {
        return $this->state(['status' => 'confirmed']);
    }

    public function completed()
    {
        return $this->state(['status' => 'completed']);
    }
}
