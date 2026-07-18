<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use App\Models\User;
use App\Models\WorkflowStage;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@osms.test',
        ]);

        $reviewer = User::factory()->reviewer()->create([
            'name' => 'Reviewer One',
            'email' => 'reviewer@osms.test',
        ]);

        User::factory()->create([
            'name' => 'Applicant Demo',
            'email' => 'applicant@osms.test',
        ]);

        $scholarship = Scholarship::factory()->create([
            'name' => 'IOU Merit Scholarship 2026',
            'created_by' => $admin->id,
        ]);

        WorkflowStage::factory()->create([
            'scholarship_id' => $scholarship->id,
            'name' => 'Screening',
            'stage_order' => 1,
            'assigned_user_id' => $reviewer->id,
        ]);

        WorkflowStage::factory()->create([
            'scholarship_id' => $scholarship->id,
            'name' => 'Final Approval',
            'stage_order' => 2,
            'assigned_user_id' => $admin->id,
        ]);
    }
}
