<?php

namespace Tests\Feature\API;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Specialty;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\TestCase;

class MedicalRecordTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeDoctor(): Doctor
    {
        $specialty = Specialty::factory()->create();
        return Doctor::factory()->create(['specialty_id' => $specialty->id]);
    }

    private function validRecordData(int $patientId, int $doctorId, array $overrides = []): array
    {
        return array_merge([
            'patient_id'  => $patientId,
            'doctor_id'   => $doctorId,
            'diagnosis'   => 'Hypertension stage 1',
            'treatment'   => 'Lifestyle changes and medication.',
            'record_type' => 'consultation',
        ], $overrides);
    }

    // --- LIST ---

    public function test_admin_can_list_all_medical_records(): void
    {
        $this->actingAsAdmin();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();
        MedicalRecord::factory()->count(3)->create([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $response = $this->getJson('/api/medical-records');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['data', 'meta' => ['total']]);
    }

    public function test_list_can_filter_by_record_type(): void
    {
        $this->actingAsAdmin();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();
        MedicalRecord::factory()->count(2)->create([
            'patient_id'  => $patient->id,
            'doctor_id'   => $doctor->id,
            'record_type' => 'consultation',
        ]);
        MedicalRecord::factory()->create([
            'patient_id'  => $patient->id,
            'doctor_id'   => $doctor->id,
            'record_type' => 'lab_result',
        ]);

        $response = $this->getJson('/api/medical-records?record_type=consultation');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_list_can_filter_by_patient(): void
    {
        $this->actingAsAdmin();
        $doctor   = $this->makeDoctor();
        $patientA = Patient::factory()->create();
        $patientB = Patient::factory()->create();
        MedicalRecord::factory()->count(2)->create(['patient_id' => $patientA->id, 'doctor_id' => $doctor->id]);
        MedicalRecord::factory()->count(4)->create(['patient_id' => $patientB->id, 'doctor_id' => $doctor->id]);

        $response = $this->getJson("/api/medical-records?patient_id={$patientA->id}");

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    // --- PATIENT NESTED ROUTE ---

    public function test_can_list_records_via_patient_route(): void
    {
        $this->actingAsAdmin();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();
        MedicalRecord::factory()->count(2)->create([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $response = $this->getJson("/api/patients/{$patient->id}/medical-records");

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_patient_nested_route_only_returns_that_patients_records(): void
    {
        $this->actingAsAdmin();
        $doctor   = $this->makeDoctor();
        $patientA = Patient::factory()->create();
        $patientB = Patient::factory()->create();
        MedicalRecord::factory()->count(2)->create(['patient_id' => $patientA->id, 'doctor_id' => $doctor->id]);
        MedicalRecord::factory()->count(5)->create(['patient_id' => $patientB->id, 'doctor_id' => $doctor->id]);

        $response = $this->getJson("/api/patients/{$patientA->id}/medical-records");

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    // --- CREATE ---

    public function test_doctor_can_create_medical_record(): void
    {
        $this->actingAsDoctor();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();

        $response = $this->postJson('/api/medical-records', $this->validRecordData($patient->id, $doctor->id));

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('medical_records', [
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
            'record_type' => 'consultation',
        ]);
    }

    public function test_record_defaults_recorded_at_to_now(): void
    {
        $this->actingAsDoctor();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();

        $this->postJson('/api/medical-records', $this->validRecordData($patient->id, $doctor->id));

        $record = MedicalRecord::first();
        $this->assertNotNull($record->recorded_at);
    }

    public function test_record_can_be_linked_to_appointment(): void
    {
        $this->actingAsAdmin();
        $doctor      = $this->makeDoctor();
        $patient     = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
        ]);

        $response = $this->postJson('/api/medical-records', $this->validRecordData($patient->id, $doctor->id, [
            'appointment_id' => $appointment->id,
        ]));

        $response->assertStatus(201);
        $this->assertDatabaseHas('medical_records', ['appointment_id' => $appointment->id]);
    }

    public function test_record_recorded_at_cannot_be_in_the_future(): void
    {
        $this->actingAsDoctor();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();

        $response = $this->postJson('/api/medical-records', $this->validRecordData($patient->id, $doctor->id, [
            'recorded_at' => now()->addDay()->toDateTimeString(),
        ]));

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['recorded_at']]);
    }

    public function test_patient_cannot_create_medical_record(): void
    {
        $this->actingAsPatient();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();

        $response = $this->postJson('/api/medical-records', $this->validRecordData($patient->id, $doctor->id));

        $response->assertStatus(403);
    }

    public function test_diagnosis_is_required(): void
    {
        $this->actingAsDoctor();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();

        $response = $this->postJson('/api/medical-records', $this->validRecordData($patient->id, $doctor->id, [
            'diagnosis' => '',
        ]));

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['diagnosis']]);
    }

    // --- SHOW ---

    public function test_admin_can_show_medical_record(): void
    {
        $this->actingAsAdmin();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();
        $record  = MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $response = $this->getJson("/api/medical-records/{$record->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $record->id);
    }

    public function test_show_returns_404_for_nonexistent_record(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/medical-records/99999');

        $response->assertStatus(404);
    }

    // --- UPDATE ---

    public function test_doctor_can_update_medical_record(): void
    {
        $this->actingAsDoctor();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();
        $record  = MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $response = $this->putJson("/api/medical-records/{$record->id}", [
            'diagnosis'    => 'Updated diagnosis',
            'prescription' => 'Lisinopril 10mg daily',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('medical_records', [
            'id'           => $record->id,
            'diagnosis'    => 'Updated diagnosis',
            'prescription' => 'Lisinopril 10mg daily',
        ]);
    }

    public function test_patient_cannot_update_medical_record(): void
    {
        $this->actingAsPatient();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();
        $record  = MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $response = $this->putJson("/api/medical-records/{$record->id}", [
            'diagnosis' => 'Hacked diagnosis',
        ]);

        $response->assertStatus(403);
    }

    // --- DELETE ---

    public function test_admin_can_delete_medical_record(): void
    {
        $this->actingAsAdmin();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();
        $record  = MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $response = $this->deleteJson("/api/medical-records/{$record->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('medical_records', ['id' => $record->id]);
    }

    public function test_doctor_cannot_delete_medical_record(): void
    {
        $this->actingAsDoctor();
        $doctor  = $this->makeDoctor();
        $patient = Patient::factory()->create();
        $record  = MedicalRecord::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
        ]);

        $response = $this->deleteJson("/api/medical-records/{$record->id}");

        $response->assertStatus(403);
    }
}
