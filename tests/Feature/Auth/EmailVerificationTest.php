<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_routes_are_disabled_for_internal_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertNotFound();
        $this->assertFalse(Route::has('verification.notice'));
        $this->assertFalse(Route::has('verification.verify'));
        $this->assertFalse(Route::has('verification.send'));
    }

    public function test_internal_users_can_access_the_dashboard_without_verifying_email(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }
}
