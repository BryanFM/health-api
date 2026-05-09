<?php

namespace Tests\Feature\API;

use App\Models\Specialty;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\TestCase;

class SpecialtyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_list_specialties(): void
    {
        $this->actingAsAdmin();
        Specialty::factory()->count(3)->create();

        $response = $this->getJson('/api/specialties');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success', 'data', 'meta' => ['total', 'per_page', 'current_page', 'last_page'],
                 ]);
    }

    public function test_admin_can_create_specialty(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/specialties', [
            'name'        => 'Cardiology',
            'description' => 'Heart conditions',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('specialties', ['name' => 'Cardiology']);
    }

    public function test_specialty_name_must_be_unique(): void
    {
        $this->actingAsAdmin();
        Specialty::factory()->create(['name' => 'Cardiology']);

        $response = $this->postJson('/api/specialties', ['name' => 'Cardiology']);

        $response->assertStatus(422);
    }

    public function test_admin_can_update_specialty(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/specialties/{$specialty->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('specialties', ['id' => $specialty->id, 'name' => 'New Name']);
    }

    public function test_admin_can_delete_specialty(): void
    {
        $this->actingAsAdmin();
        $specialty = Specialty::factory()->create();

        $response = $this->deleteJson("/api/specialties/{$specialty->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_patient_cannot_create_specialty(): void
    {
        $this->actingAsPatient();

        $response = $this->postJson('/api/specialties', ['name' => 'Test']);

        $response->assertStatus(403);
    }
}
