<?php

namespace Database\Factories;

use App\Models\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpecialtyFactory extends Factory
{
    protected $model = Specialty::class;

    private static array $specialties = [
        'Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics',
        'Dermatology', 'Oncology', 'Psychiatry', 'Ophthalmology',
        'Gynecology', 'Endocrinology', 'Gastroenterology', 'Urology',
    ];

    public function definition()
    {
        return [
            'name'        => $this->faker->unique()->randomElement(self::$specialties),
            'description' => $this->faker->sentence(),
            'is_active'   => true,
        ];
    }
}
