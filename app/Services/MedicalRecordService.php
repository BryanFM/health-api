<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\User;
use App\Traits\ResolvesOwnership;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MedicalRecordService
{
    use ResolvesOwnership;

    public function list(array $filters = [], int $perPage = 15, ?User $user = null): LengthAwarePaginator
    {
        $query = MedicalRecord::with(['patient.user', 'doctor.user', 'appointment']);

        if ($user) {
            $query = $this->applyMedicalRecordOwnership($query, $user);
        }

        return $query
            ->when(isset($filters['patient_id']), fn($q) => $q->where('patient_id', $filters['patient_id']))
            ->when(isset($filters['doctor_id']), fn($q) => $q->where('doctor_id', $filters['doctor_id']))
            ->when(isset($filters['record_type']), fn($q) => $q->where('record_type', $filters['record_type']))
            ->latest('recorded_at')
            ->paginate($perPage);
    }

    public function findOrFail(int $id, ?User $user = null): MedicalRecord
    {
        $record = MedicalRecord::with(['patient.user', 'doctor.user', 'appointment'])->findOrFail($id);

        if ($user && !$this->userCanAccessMedicalRecord($record, $user)) {
            throw new AuthorizationException('You do not have access to this medical record.');
        }

        return $record;
    }

    public function create(array $data): MedicalRecord
    {
        return MedicalRecord::create($data);
    }

    public function update(MedicalRecord $record, array $data): MedicalRecord
    {
        $record->update($data);

        return $record->fresh(['patient.user', 'doctor.user', 'appointment']);
    }

    public function delete(MedicalRecord $record): void
    {
        $record->delete();
    }

    public function listForPatient(int $patientId, array $filters = [], int $perPage = 15, ?User $user = null): LengthAwarePaginator
    {
        if ($user && $user->hasRole('patient')) {
            $ownPatientId = $this->resolvePatientId($user);
            if ($ownPatientId !== $patientId) {
                throw new AuthorizationException('You do not have access to this patient\'s medical records.');
            }
        }

        return $this->list(array_merge($filters, ['patient_id' => $patientId]), $perPage);
    }
}
