<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-04 to FR-09 — application form data, one-per-scholarship rule (BR-01),
 * draft support, reference numbers, and submission timestamp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 20)->unique()->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('scholarship_id')->constrained('scholarships');

            // FR-04: application form fields
            $table->string('programme_of_study')->nullable();
            $table->unsignedTinyInteger('year_of_study')->nullable();
            $table->string('nationality')->nullable();
            $table->decimal('gpa_submitted', 3, 2)->nullable();
            $table->boolean('financial_need_declared')->default(false);
            $table->text('personal_statement')->nullable();
            $table->string('referee_name')->nullable();
            $table->string('referee_email')->nullable();

            // FR-07: draft vs submitted; FR-12: eligibility outcome;
            // FR-16/17: workflow progression.
            $table->enum('status', [
                'draft',
                'submitted',
                'eligible',
                'ineligible',
                'in_review',
                'approved',
                'rejected',
                'returned_for_revision',
            ])->default('draft');

            $table->unsignedTinyInteger('current_stage_order')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            // BR-01: one active application per applicant per scholarship
            $table->unique(['user_id', 'scholarship_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
