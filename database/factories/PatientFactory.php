<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition()
    {
        return [
            'user_id'                 => User::factory(),
            'date_of_birth'           => $this->faker->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'gender'                  => $this->faker->randomElement(['male', 'female', 'other']),
            'blood_type'              => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'address'                 => $this->faker->address(),
            'emergency_contact_name'  => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'allergies'               => $this->faker->boolean(30) ? $this->faker->words(3, true) : null,
        ];
    }
}
