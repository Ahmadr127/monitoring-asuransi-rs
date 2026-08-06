<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('config:clear');
    }

    protected function createAdminUser(array $overrides = []): User
    {
        return User::factory()->admin()->create($overrides);
    }

    protected function createNonAdminUser(array $overrides = []): User
    {
        return User::factory()->nonAdmin()->create($overrides);
    }

    protected function loginAsAdmin(): User
    {
        $user = $this->createAdminUser();
        $this->actingAs($user);

        return $user;
    }

    protected function loginAsNonAdmin(): User
    {
        $user = $this->createNonAdminUser();
        $this->actingAs($user);

        return $user;
    }
}
