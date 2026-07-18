<?php

namespace Database\Factories;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'scholarship_id' => Scholarship::factory(),
            'programme_of_study' => 'Information Technology',
            'year_of_study' => 3,
            'nationality' => 'Nigerian',
            'gpa_submitted' => 3.5,
            'financial_need_declared' => true,
            'personal_statement' => fake()->paragraphs(2, true),
            'referee_name' => fake()->name(),
            'referee_email' => fake()->safeEmail(),
            'status' => 'draft',
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => 'submitted',
            'reference_no' => 'OSMS-'.now()->format('Y').'-'.strtoupper(fake()->bothify('??####')),
            'submitted_at' => now(),
        ]);
    }
}
