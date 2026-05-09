<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function createUserWithRole(string $role, array $attributes = []): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);

        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    protected function actingAsAdmin(): User
    {
        $user = $this->createUserWithRole('admin');
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    protected function actingAsDoctor(): User
    {
        $user = $this->createUserWithRole('doctor');
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    protected function actingAsPatient(): User
    {
        $user = $this->createUserWithRole('patient');
        $this->actingAs($user, 'sanctum');
        return $user;
    }
}
