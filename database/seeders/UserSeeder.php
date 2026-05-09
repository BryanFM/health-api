<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $specialty = Specialty::first() ?? Specialty::factory()->create(['name' => 'General Medicine']);

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@healthapi.com'],
            [
                'name'     => 'Admin User',
                'phone'    => '555-0001',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        // Doctor
        $doctorUser = User::firstOrCreate(
            ['email' => 'doctor@healthapi.com'],
            [
                'name'     => 'Dr. John Smith',
                'phone'    => '555-0002',
                'password' => Hash::make('password'),
            ]
        );
        $doctorUser->assignRole('doctor');
        Doctor::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'specialty_id'   => $specialty->id,
                'license_number' => 'MD-12345',
                'experience_years' => 10,
                'bio'            => 'Experienced general practitioner.',
            ]
        );

        // Patient
        $patientUser = User::firstOrCreate(
            ['email' => 'patient@healthapi.com'],
            [
                'name'     => 'Jane Doe',
                'phone'    => '555-0003',
                'password' => Hash::make('password'),
            ]
        );
        $patientUser->assignRole('patient');
        Patient::firstOrCreate(
            ['user_id' => $patientUser->id],
            [
                'date_of_birth' => '1990-06-15',
                'gender'        => 'female',
                'blood_type'    => 'O+',
                'address'       => '123 Main St, Springfield',
                'emergency_contact_name'  => 'John Doe',
                'emergency_contact_phone' => '555-0099',
            ]
        );
    }
}
