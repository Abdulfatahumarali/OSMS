<?php

namespace Database\Factories;

use App\Models\Scholarship;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowStageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'scholarship_id' => Scholarship::factory(),
            'name' => 'Screening',
            'stage_order' => 1,
            'assigned_role' => 'reviewer',
        ];
    }
}
