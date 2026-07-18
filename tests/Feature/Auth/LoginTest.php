<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\CreatesApplication;
use Tests\TestCase;

/**
 * FR-02 — authentication.
 */
class LoginTest extends TestCase
{
    use CreatesApplication, RefreshDatabase;

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('scholarships.index'));
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_cannot_view_scholarships_without_logging_in(): void
    {
        $response = $this->get('/scholarships');

        $response->assertRedirect(route('login'));
    }
}
