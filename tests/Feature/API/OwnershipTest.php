<?php

namespace Tests\Feature\API;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\TestCase;

class OwnershipTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeDoctor(): Doctor
    {
        $specialty = Specialty::factory()->create();
        $user      = User::factory()->create();
        $user->assignRole('doctor');
        return Doctor::factory()->create(['specialty_id' => $specialty->id, 'user_id' => $user->id]);
    }

    private function makePatientWithUser(): array
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);
        return [$user, $patient];
    }

    // =========================================================
    // APPOINTMENTS — patient sees only their own
    // =========================================================

    public function test_patient_only_sees_own_appointments_in_list(): void
    {
        [$userA, $patientA] = $this->makePatientWithUser();
        [$userB, $patientB] = $this->makePatientWithUser();
        $doctor = $this->makeDoctor();

        Appointment::factory()->count(2)->create(['patient_id' => $patientA->id, 'doctor_id' => $doctor->id]);
        Appointment::factory()->count(3)->create(['patient_id' => $patientB->id, 'doctor_id' => $doctor->id]);

        $this->actingAs($userA, 'sanctum');
        $response = $this->getJson('/api/appointments');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_patient_cannot_see_another_patients_appointment(): void
    {
        [$userA, $patientA] = $this->makePatientWithUser();
        [$userB, $patientB] = $this->makePatientWithUser();
        $doctor      = $this->makeDoctor();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patientB->id,
            'doctor_id'  => $doctor->id,
        ]);

        $this->actingAs($userA, 'sanctum');
        $response = $this->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(403);
    }

    public function test_patient_can_see_own_appointment(): void
    {
        [$user, $patient] = $this->makePatientWithUser();
        $doctor      = $this->makeDoctor();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $this->actingAs($user, 'sanctum');
        $response = $this->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $appointment->id);
    }

    // =========================================================
    // APPOINTMENTS — doctor sees only their own
    // =========================================================

    public function test_doctor_only_sees_own_appointments_in_list(): void
    {
        $specialty = Specialty::factory()->create();

        $userDocA = User::factory()->create();
        $userDocA->assignRole('doctor');
        $doctorA = Doctor::factory()->create(['specialty_id' => $specialty->id, 'user_id' => $userDocA->id]);

        $userDocB = User::factory()->create();
        $userDocB->assignRole('doctor');
        $doctorB = Doctor::factory()->create(['specialty_id' => $specialty->id, 'user_id' => $userDocB->id]);

        [, $patient] = $this->makePatientWithUser();

        Appointment::factory()->count(2)->create(['doctor_id' => $doctorA->id, 'patient_id' => $patient->id]);
        Appointment::factory()->count(4)->create(['doctor_id' => $doctorB->id, 'patient_id' => $patient->id]);

        $this->actingAs($userDocA, 'sanctum');
        $response = $this->getJson('/api/appointments');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_admin_sees_all_appointments(): void
    {
        $this->actingAsAdmin();
        $doctor  = $this->makeDoctor();
        [, $p1]  = $this->makePatientWithUser();
        [, $p2]  = $this->makePatientWithUser();

        Appointment::factory()->count(2)->create(['doctor_id' => $doctor->id, 'patient_id' => $p1->id]);
        Appointment::factory()->count(3)->create(['doctor_id' => $doctor->id, 'patient_id' => $p2->id]);

        $response = $this->getJson('/api/appointments');

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('meta.total'));
    }

    // =========================================================
    // MEDICAL RECORDS — patient sees only their own
    // =========================================================

    public function test_patient_only_sees_own_medical_records_in_list(): void
    {
        [$userA, $patientA] = $this->makePatientWithUser();
        [$userB, $patientB] = $this->makePatientWithUser();
        $doctor = $this->makeDoctor();

        MedicalRecord::factory()->count(2)->create(['patient_id' => $patientA->id, 'doctor_id' => $doctor->id]);
        MedicalRecord::factory()->count(5)->create(['patient_id' => $patientB->id, 'doctor_id' => $doctor->id]);

        $this->actingAs($userA, 'sanctum');
        $response = $this->getJson('/api/medical-records');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_patient_cannot_see_another_patients_medical_record(): void
    {
        [$userA]        = $this->makePatientWithUser();
        [, $patientB]   = $this->makePatientWithUser();
        $doctor         = $this->makeDoctor();
        $record         = MedicalRecord::factory()->create([
            'patient_id' => $patientB->id,
            'doctor_id'  => $doctor->id,
        ]);

        $this->actingAs($userA, 'sanctum');
        $response = $this->getJson("/api/medical-records/{$record->id}");

        $response->assertStatus(403);
    }

    public function test_patient_can_see_own_medical_record(): void
    {
        [$user, $patient] = $this->makePatientWithUser();
        $doctor  = $this->makeDoctor();
        $record  = MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $this->actingAs($user, 'sanctum');
        $response = $this->getJson("/api/medical-records/{$record->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $record->id);
    }

    public function test_patient_cannot_access_another_patients_nested_medical_records(): void
    {
        [$userA]       = $this->makePatientWithUser();
        [, $patientB]  = $this->makePatientWithUser();

        $this->actingAs($userA, 'sanctum');
        $response = $this->getJson("/api/patients/{$patientB->id}/medical-records");

        $response->assertStatus(403);
    }

    public function test_patient_can_access_own_nested_medical_records(): void
    {
        [$user, $patient] = $this->makePatientWithUser();
        $doctor = $this->makeDoctor();
        MedicalRecord::factory()->count(2)->create([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $this->actingAs($user, 'sanctum');
        $response = $this->getJson("/api/patients/{$patient->id}/medical-records");

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_admin_sees_all_medical_records(): void
    {
        $this->actingAsAdmin();
        $doctor = $this->makeDoctor();
        [, $p1] = $this->makePatientWithUser();
        [, $p2] = $this->makePatientWithUser();

        MedicalRecord::factory()->count(3)->create(['patient_id' => $p1->id, 'doctor_id' => $doctor->id]);
        MedicalRecord::factory()->count(4)->create(['patient_id' => $p2->id, 'doctor_id' => $doctor->id]);

        $response = $this->getJson('/api/medical-records');

        $response->assertStatus(200);
        $this->assertEquals(7, $response->json('meta.total'));
    }
}
