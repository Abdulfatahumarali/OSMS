<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;
use Tests\TestCase;

/**
 * FR-01 — user registration.
 */
class RegistrationTest extends TestCase
{
    use CreatesApplication, RefreshDatabase;

    public function test_new_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane Applicant',
            'email' => 'jane@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('scholarships.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'applicant',
        ]);
    }

    public function test_registration_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'name' => 'Duplicate',
            'email' => 'taken@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane Applicant',
            'email' => 'jane2@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Mismatch123!',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
