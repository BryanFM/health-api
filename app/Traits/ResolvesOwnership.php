<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ResolvesOwnership
{
    protected function resolvePatientId(User $user): ?int
    {
        if ($user->hasRole('patient')) {
            $id = $user->patient()->value('id');
            return $id !== null ? (int) $id : null;
        }

        return null;
    }

    protected function resolveDoctorId(User $user): ?int
    {
        if ($user->hasRole('doctor')) {
            $id = $user->doctor()->value('id');
            return $id !== null ? (int) $id : null;
        }

        return null;
    }

    protected function applyPatientOwnership(Builder $query, User $user, string $column = 'patient_id'): Builder
    {
        if ($user->hasRole('patient')) {
            $patientId = $this->resolvePatientId($user);
            return $query->where($column, $patientId);
        }

        if ($user->hasRole('doctor')) {
            $doctorId = $this->resolveDoctorId($user);
            return $query->whereHas('patient.appointments', function ($q) use ($doctorId) {
                $q->where('doctor_id', $doctorId);
            });
        }

        return $query;
    }

    protected function applyAppointmentOwnership(Builder $query, User $user): Builder
    {
        if ($user->hasRole('patient')) {
            return $query->where('patient_id', $this->resolvePatientId($user));
        }

        if ($user->hasRole('doctor')) {
            return $query->where('doctor_id', $this->resolveDoctorId($user));
        }

        return $query;
    }

    protected function applyMedicalRecordOwnership(Builder $query, User $user): Builder
    {
        if ($user->hasRole('patient')) {
            return $query->where('patient_id', $this->resolvePatientId($user));
        }

        if ($user->hasRole('doctor')) {
            return $query->where('doctor_id', $this->resolveDoctorId($user));
        }

        return $query;
    }

    protected function userCanAccessAppointment(\App\Models\Appointment $appointment, User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('patient')) {
            return (int) $appointment->patient_id === $this->resolvePatientId($user);
        }

        if ($user->hasRole('doctor')) {
            return (int) $appointment->doctor_id === $this->resolveDoctorId($user);
        }

        return false;
    }

    protected function userCanAccessMedicalRecord(\App\Models\MedicalRecord $record, User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('patient')) {
            return (int) $record->patient_id === $this->resolvePatientId($user);
        }

        if ($user->hasRole('doctor')) {
            return (int) $record->doctor_id === $this->resolveDoctorId($user);
        }

        return false;
    }
}
