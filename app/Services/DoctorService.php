<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Doctor::with(['user', 'specialty'])
            ->when(isset($filters['search']), fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$filters['search']}%")))
            ->when(isset($filters['specialty_id']), fn($q) => $q->where('specialty_id', $filters['specialty_id']))
            ->when(isset($filters['is_available']), fn($q) => $q->where('is_available', $filters['is_available']))
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Doctor
    {
        return Doctor::with(['user', 'specialty'])->findOrFail($id);
    }

    public function create(array $data): Doctor
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole('doctor');

            return Doctor::create([
                'user_id'          => $user->id,
                'specialty_id'     => $data['specialty_id'],
                'license_number'   => $data['license_number'],
                'experience_years' => $data['experience_years'] ?? 0,
                'bio'              => $data['bio'] ?? null,
                'is_available'     => $data['is_available'] ?? true,
            ]);
        });
    }

    public function update(Doctor $doctor, array $data): Doctor
    {
        return DB::transaction(function () use ($doctor, $data) {
            $userFields = array_filter([
                'name'  => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            if (!empty($userFields)) {
                $doctor->user->update($userFields);
            }

            $doctor->update(array_filter([
                'specialty_id'     => $data['specialty_id'] ?? null,
                'license_number'   => $data['license_number'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'bio'              => $data['bio'] ?? null,
                'is_available'     => $data['is_available'] ?? null,
            ], fn($v) => $v !== null));

            return $doctor->fresh(['user', 'specialty']);
        });
    }

    public function delete(Doctor $doctor): void
    {
        DB::transaction(function () use ($doctor) {
            $doctor->delete();
            $doctor->user->delete();
        });
    }
}
