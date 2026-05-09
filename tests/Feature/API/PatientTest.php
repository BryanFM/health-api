<?php

namespace Tests\Feature\API;

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PatientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function validPatientData(array $overrides = []): array
    {
        return array_merge([
            'name'          => 'Jane Doe',
            'email'         => 'jane@example.com',
            'password'      => 'Password1',
            'date_of_birth' => '1990-01-15',
            'gender'        => 'female',
            'blood_type'    => 'O+',
        ], $overrides);
    }

    public function test_admin_can_list_patients(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/patients');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['data', 'meta']);
    }

    public function test_admin_can_create_patient(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/patients', $this->validPatientData());

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
        $this->assertDatabaseHas('patients', ['gender' => 'female']);
    }

    public function test_create_patient_fails_with_invalid_email(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/patients', $this->validPatientData([
            'email' => 'not-an-email',
        ]));

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_create_patient_fails_with_duplicate_email(): void
    {
        $this->actingAsAdmin();
        User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->postJson('/api/patients', $this->validPatientData());

        $response->assertStatus(422);
    }

    public function test_admin_can_show_patient(): void
    {
        $this->actingAsAdmin();
        $patient = Patient::factory()->create();

        $response = $this->getJson("/api/patients/{$patient->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_admin_can_update_patient(): void
    {
        $this->actingAsAdmin();
        $patient = Patient::factory()->create();

        $response = $this->putJson("/api/patients/{$patient->id}", [
            'address' => '456 New Street',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_admin_can_soft_delete_patient(): void
    {
        $this->actingAsAdmin();
        $patient = Patient::factory()->create();

        $response = $this->deleteJson("/api/patients/{$patient->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('patients', ['id' => $patient->id]);
    }

    public function test_shows_404_for_nonexistent_patient(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/patients/99999');

        $response->assertStatus(404);
    }
}
