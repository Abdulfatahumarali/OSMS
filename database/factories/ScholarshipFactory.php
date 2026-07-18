<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScholarshipFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Scholarship',
            'description' => fake()->paragraph(),
            'award_value' => fake()->randomFloat(2, 500, 5000),
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addMonth(),
            'min_gpa' => 3.0,
            'programme_of_study' => 'Information Technology',
            'min_year_of_study' => 2,
            'nationality' => null,
            'requires_financial_need' => false,
            'is_published' => true,
            'created_by' => User::factory()->admin(),
        ];
    }
}
