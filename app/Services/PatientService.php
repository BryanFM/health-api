<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Patient::with(['user'])
            ->when(isset($filters['search']), fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$filters['search']}%")))
            ->when(isset($filters['gender']), fn($q) => $q->where('gender', $filters['gender']))
            ->when(isset($filters['blood_type']), fn($q) => $q->where('blood_type', $filters['blood_type']))
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Patient
    {
        return Patient::with(['user'])->findOrFail($id);
    }

    public function create(array $data): Patient
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole('patient');

            return Patient::create([
                'user_id'                 => $user->id,
                'date_of_birth'           => $data['date_of_birth'],
                'gender'                  => $data['gender'],
                'blood_type'              => $data['blood_type'] ?? null,
                'address'                 => $data['address'] ?? null,
                'emergency_contact_name'  => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'allergies'               => $data['allergies'] ?? null,
            ]);
        });
    }

    public function update(Patient $patient, array $data): Patient
    {
        return DB::transaction(function () use ($patient, $data) {
            $userFields = array_filter([
                'name'  => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            if (!empty($userFields)) {
                $patient->user->update($userFields);
            }

            $patient->update(array_filter([
                'date_of_birth'           => $data['date_of_birth'] ?? null,
                'gender'                  => $data['gender'] ?? null,
                'blood_type'              => $data['blood_type'] ?? null,
                'address'                 => $data['address'] ?? null,
                'emergency_contact_name'  => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'allergies'               => $data['allergies'] ?? null,
            ], fn($v) => $v !== null));

            return $patient->fresh(['user']);
        });
    }

    public function delete(Patient $patient): void
    {
        DB::transaction(function () use ($patient) {
            $patient->delete();
            $patient->user->delete();
        });
    }
}
