<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-19, FR-20 — records each reviewer's decision (Approve / Reject /
 * Return for Revision) with an audit trail, per workflow stage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications');
            $table->foreignId('workflow_stage_id')->constrained('workflow_stages');
            $table->foreignId('reviewer_id')->constrained('users');
            $table->enum('decision', ['approve', 'reject', 'return_for_revision']);
            $table->text('comments')->nullable();
            $table->timestamp('decided_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_decisions');
    }
};
