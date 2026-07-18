<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;
use Tests\TestCase;

/**
 * FR-03 — scholarship listing.
 */
class ScholarshipListingTest extends TestCase
{
    use CreatesApplication, RefreshDatabase;

    public function test_authenticated_user_can_view_published_open_scholarships(): void
    {
        $user = User::factory()->create();
        $scholarship = Scholarship::factory()->create(['is_published' => true, 'name' => 'Test Scholarship']);

        $response = $this->actingAs($user)->get('/scholarships');

        $response->assertOk();
        $response->assertSee('Test Scholarship');
    }

    public function test_unpublished_scholarships_are_not_listed(): void
    {
        $user = User::factory()->create();
        Scholarship::factory()->create(['is_published' => false, 'name' => 'Hidden Scholarship']);

        $response = $this->actingAs($user)->get('/scholarships');

        $response->assertDontSee('Hidden Scholarship');
    }
}
