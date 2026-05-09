<?php

namespace App\Services;

use App\Models\Specialty;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SpecialtyService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Specialty::withCount('doctors')
            ->when(isset($filters['search']), fn($q) => $q->where('name', 'like', "%{$filters['search']}%"))
            ->when(isset($filters['is_active']), fn($q) => $q->where('is_active', $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Specialty
    {
        return Specialty::withCount('doctors')->findOrFail($id);
    }

    public function create(array $data): Specialty
    {
        return Specialty::create($data);
    }

    public function update(Specialty $specialty, array $data): Specialty
    {
        $specialty->update($data);

        return $specialty->fresh();
    }

    public function delete(Specialty $specialty): void
    {
        $specialty->delete();
    }
}
