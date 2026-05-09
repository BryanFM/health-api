<?php

namespace Tests\Feature\API;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_list_appointments(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/appointments');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'meta']);
    }

    public function test_admin_can_create_appointment(): void
    {
        $this->actingAsAdmin();

        $specialty = Specialty::factory()->create();
        $doctor    = Doctor::factory()->create(['specialty_id' => $specialty->id]);
        $patient   = Patient::factory()->create();

        $response = $this->postJson('/api/appointments', [
            'patient_id'   => $patient->id,
            'doctor_id'    => $doctor->id,
            'scheduled_at' => now()->addDays(3)->toDateTimeString(),
            'reason'       => 'Routine check-up',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);
    }

    public function test_scheduled_at_must_be_in_the_future(): void
    {
        $this->actingAsAdmin();

        $specialty = Specialty::factory()->create();
        $doctor    = Doctor::factory()->create(['specialty_id' => $specialty->id]);
        $patient   = Patient::factory()->create();

        $response = $this->postJson('/api/appointments', [
            'patient_id'   => $patient->id,
            'doctor_id'    => $doctor->id,
            'scheduled_at' => now()->subDay()->toDateTimeString(),
            'reason'       => 'Past appointment',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['scheduled_at']]);
    }

    public function test_admin_can_cancel_appointment(): void
    {
        $this->actingAsAdmin();
        $specialty   = Specialty::factory()->create();
        $doctor      = Doctor::factory()->create(['specialty_id' => $specialty->id]);
        $patient     = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
            'status'     => 'confirmed',
        ]);

        $response = $this->postJson("/api/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => 'Doctor unavailable',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('appointments', [
            'id'     => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cannot_cancel_already_completed_appointment(): void
    {
        $this->actingAsAdmin();
        $specialty   = Specialty::factory()->create();
        $doctor      = Doctor::factory()->create(['specialty_id' => $specialty->id]);
        $patient     = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
            'status'     => 'completed',
        ]);

        $response = $this->postJson("/api/appointments/{$appointment->id}/cancel", [
            'cancellation_reason' => 'Too late',
        ]);

        $response->assertStatus(422);
    }
}
