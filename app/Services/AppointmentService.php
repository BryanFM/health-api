<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use App\Traits\ResolvesOwnership;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    use ResolvesOwnership;

    public function list(array $filters = [], int $perPage = 15, ?User $user = null): LengthAwarePaginator
    {
        $query = Appointment::with(['patient.user', 'doctor.user', 'doctor.specialty']);

        if ($user) {
            $query = $this->applyAppointmentOwnership($query, $user);
        }

        return $query
            ->when(isset($filters['patient_id']), fn($q) => $q->where('patient_id', $filters['patient_id']))
            ->when(isset($filters['doctor_id']), fn($q) => $q->where('doctor_id', $filters['doctor_id']))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['date']), fn($q) => $q->whereDate('scheduled_at', $filters['date']))
            ->when(isset($filters['upcoming']), fn($q) => $q->upcoming())
            ->latest('scheduled_at')
            ->paginate($perPage);
    }

    public function findOrFail(int $id, ?User $user = null): Appointment
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user', 'doctor.specialty', 'medicalRecord'])
            ->findOrFail($id);

        if ($user && !$this->userCanAccessAppointment($appointment, $user)) {
            throw new AuthorizationException('You do not have access to this appointment.');
        }

        return $appointment;
    }

    public function create(array $data): Appointment
    {
        $this->ensureNoOverlap($data['doctor_id'], $data['scheduled_at'], $data['duration_minutes'] ?? 30);

        return Appointment::create($data);
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        if (isset($data['scheduled_at'])) {
            $this->ensureNoOverlap(
                $data['doctor_id'] ?? $appointment->doctor_id,
                $data['scheduled_at'],
                $data['duration_minutes'] ?? $appointment->duration_minutes,
                $appointment->id
            );
        }

        $appointment->update($data);

        return $appointment->fresh(['patient.user', 'doctor.user', 'doctor.specialty']);
    }

    public function cancel(Appointment $appointment, string $reason): Appointment
    {
        if (in_array($appointment->status, ['completed', 'cancelled'])) {
            throw ValidationException::withMessages([
                'status' => "Cannot cancel an appointment with status '{$appointment->status}'.",
            ]);
        }

        $appointment->update([
            'status'              => 'cancelled',
            'cancellation_reason' => $reason,
        ]);

        return $appointment->fresh();
    }

    public function delete(Appointment $appointment): void
    {
        $appointment->delete();
    }

    private function ensureNoOverlap(int $doctorId, string $scheduledAt, int $durationMinutes, ?int $excludeId = null): void
    {
        $newStart = \Carbon\Carbon::parse($scheduledAt);
        $newEnd   = $newStart->copy()->addMinutes($durationMinutes);

        $existing = Appointment::where('doctor_id', $doctorId)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->when($excludeId, function ($q) use ($excludeId) {
                $q->where('id', '!=', $excludeId);
            })
            ->get(['scheduled_at', 'duration_minutes']);

        foreach ($existing as $appt) {
            $existingStart = \Carbon\Carbon::parse($appt->scheduled_at);
            $existingEnd   = $existingStart->copy()->addMinutes($appt->duration_minutes);

            if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'The doctor already has an appointment scheduled during this time slot.',
                ]);
            }
        }
    }
}
