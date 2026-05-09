<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition()
    {
        return [
            'user_id'          => User::factory(),
            'specialty_id'     => Specialty::factory(),
            'license_number'   => strtoupper($this->faker->bothify('??-#####')),
            'experience_years' => $this->faker->numberBetween(1, 30),
            'bio'              => $this->faker->paragraph(),
            'is_available'     => true,
        ];
    }
}
