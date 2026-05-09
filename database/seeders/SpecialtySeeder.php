<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run()
    {
        $specialties = [
            ['name' => 'General Medicine',   'description' => 'Primary care and general health management.'],
            ['name' => 'Cardiology',          'description' => 'Diagnosis and treatment of heart conditions.'],
            ['name' => 'Neurology',           'description' => 'Disorders of the nervous system.'],
            ['name' => 'Orthopedics',         'description' => 'Musculoskeletal system disorders.'],
            ['name' => 'Pediatrics',          'description' => 'Medical care for infants, children, and adolescents.'],
            ['name' => 'Dermatology',         'description' => 'Skin, hair, and nail conditions.'],
            ['name' => 'Psychiatry',          'description' => 'Mental health disorders.'],
            ['name' => 'Gynecology',          'description' => "Women's reproductive health."],
            ['name' => 'Ophthalmology',       'description' => 'Eye disorders and vision care.'],
            ['name' => 'Endocrinology',       'description' => 'Hormonal and metabolic disorders.'],
        ];

        foreach ($specialties as $specialty) {
            Specialty::firstOrCreate(['name' => $specialty['name']], $specialty);
        }
    }
}
