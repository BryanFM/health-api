<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // patients
            'patients.index',
            'patients.show',
            'patients.create',
            'patients.update',
            'patients.delete',

            // doctors
            'doctors.index',
            'doctors.show',
            'doctors.create',
            'doctors.update',
            'doctors.delete',

            // specialties
            'specialties.index',
            'specialties.show',
            'specialties.create',
            'specialties.update',
            'specialties.delete',

            // appointments
            'appointments.index',
            'appointments.show',
            'appointments.create',
            'appointments.update',
            'appointments.delete',
            'appointments.cancel',

            // medical records
            'medical-records.index',
            'medical-records.show',
            'medical-records.create',
            'medical-records.update',
            'medical-records.delete',

            // users
            'users.index',
            'users.show',
            'users.create',
            'users.update',
            'users.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $doctor = Role::firstOrCreate(['name' => 'doctor']);
        $doctor->syncPermissions([
            'patients.index',
            'patients.show',
            'appointments.index',
            'appointments.show',
            'appointments.update',
            'medical-records.index',
            'medical-records.show',
            'medical-records.create',
            'medical-records.update',
            'specialties.index',
            'specialties.show',
        ]);

        $patient = Role::firstOrCreate(['name' => 'patient']);
        $patient->syncPermissions([
            'appointments.index',
            'appointments.show',
            'appointments.create',
            'appointments.cancel',
            'medical-records.index',
            'medical-records.show',
            'specialties.index',
            'specialties.show',
            'doctors.index',
            'doctors.show',
        ]);
    }
}
