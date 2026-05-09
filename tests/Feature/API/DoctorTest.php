<?php

namespace Tests\Feature\API;

use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\TestCase;

class DoctorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function validDoctorData(int $specialtyId, array $overrides = []): array
    {
        return array_merge([
            'name'             => 'Dr. House',
            'email'            => 'house@example.com',
            'password'         => 'Password1',
            'specialty_id'     => $specialtyId,
            'license_number'   => 'MD-99999',
            'experience_years' => 15,
            'bio'              => 'Diagnostic medicine expert.',
        ], $overrides);
    }

    // --- LIST ---

    public function test_admin_can_list_doctors(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create();
        Doctor::factory()->count(3)->create(['specialty_id' => $specialty->id]);

        $response = $this->getJson('/api/doctors');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['data', 'meta' => ['total', 'per_page']]);
    }

    public function test_patient_can_list_doctors(): void
    {
        $this->actingAsPatient();

        $response = $this->getJson('/api/doctors');

        $response->assertStatus(200);
    }

    public function test_list_can_filter_by_specialty(): void
    {
        $this->actingAsAdmin();
        $specialtyA = Specialty::factory()->create();
        $specialtyB = Specialty::factory()->create();
        Doctor::factory()->count(2)->create(['specialty_id' => $specialtyA->id]);
        Doctor::factory()->count(3)->create(['specialty_id' => $specialtyB->id]);

        $response = $this->getJson("/api/doctors?specialty_id={$specialtyA->id}");

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_list_can_filter_by_availability(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create();
        Doctor::factory()->count(2)->create(['specialty_id' => $specialty->id, 'is_available' => true]);
        Doctor::factory()->count(1)->create(['specialty_id' => $specialty->id, 'is_available' => false]);

        $response = $this->getJson('/api/doctors?is_available=1');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    // --- CREATE ---

    public function test_admin_can_create_doctor(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create();

        $response = $this->postJson('/api/doctors', $this->validDoctorData($specialty->id));

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['email' => 'house@example.com']);
        $this->assertDatabaseHas('doctors', ['license_number' => 'MD-99999']);
    }

    public function test_creating_doctor_assigns_doctor_role(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create();

        $this->postJson('/api/doctors', $this->validDoctorData($specialty->id));

        $user = User::where('email', 'house@example.com')->first();
        $this->assertTrue($user->hasRole('doctor'));
    }

    public function test_license_number_must_be_unique(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create();
        Doctor::factory()->create(['specialty_id' => $specialty->id, 'license_number' => 'MD-99999']);

        $response = $this->postJson('/api/doctors', $this->validDoctorData($specialty->id));

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['license_number']]);
    }

    public function test_specialty_must_exist(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/doctors', $this->validDoctorData(99999));

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['specialty_id']]);
    }

    public function test_email_must_be_unique(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create();
        User::factory()->create(['email' => 'house@example.com']);

        $response = $this->postJson('/api/doctors', $this->validDoctorData($specialty->id));

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_patient_cannot_create_doctor(): void
    {
        $this->actingAsPatient();
        $specialty = Specialty::factory()->create();

        $response = $this->postJson('/api/doctors', $this->validDoctorData($specialty->id));

        $response->assertStatus(403);
    }

    // --- SHOW ---

    public function test_admin_can_show_doctor(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create();
        $doctor    = Doctor::factory()->create(['specialty_id' => $specialty->id]);

        $response = $this->getJson("/api/doctors/{$doctor->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.id', $doctor->id);
    }

    public function test_show_returns_404_for_nonexistent_doctor(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/doctors/99999');

        $response->assertStatus(404);
    }

    // --- UPDATE ---

    public function test_admin_can_update_doctor_bio(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create();
        $doctor    = Doctor::factory()->create(['specialty_id' => $specialty->id]);

        $response = $this->putJson("/api/doctors/{$doctor->id}", [
            'bio'          => 'Updated biography.',
            'is_available' => false,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('doctors', [
            'id'           => $doctor->id,
            'is_available' => false,
        ]);
    }

    public function test_admin_can_change_doctor_specialty(): void
    {
        $this->actingAsAdmin();
        $specialtyA = Specialty::factory()->create();
        $specialtyB = Specialty::factory()->create();
        $doctor     = Doctor::factory()->create(['specialty_id' => $specialtyA->id]);

        $response = $this->putJson("/api/doctors/{$doctor->id}", [
            'specialty_id' => $specialtyB->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('doctors', [
            'id'           => $doctor->id,
            'specialty_id' => $specialtyB->id,
        ]);
    }

    public function test_update_license_number_unique_ignores_own_record(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create();
        $doctor    = Doctor::factory()->create([
            'specialty_id'   => $specialty->id,
            'license_number' => 'MD-SAME',
        ]);

        $response = $this->putJson("/api/doctors/{$doctor->id}", [
            'license_number' => 'MD-SAME',
        ]);

        $response->assertStatus(200);
    }

    // --- DELETE ---

    public function test_admin_can_soft_delete_doctor(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create();
        $doctor    = Doctor::factory()->create(['specialty_id' => $specialty->id]);

        $response = $this->deleteJson("/api/doctors/{$doctor->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('doctors', ['id' => $doctor->id]);
        $this->assertSoftDeleted('users', ['id' => $doctor->user_id]);
    }

    public function test_patient_cannot_delete_doctor(): void
    {
        $this->actingAsPatient();
        $specialty = Specialty::factory()->create();
        $doctor    = Doctor::factory()->create(['specialty_id' => $specialty->id]);

        $response = $this->deleteJson("/api/doctors/{$doctor->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('doctors', ['id' => $doctor->id, 'deleted_at' => null]);
    }
}
