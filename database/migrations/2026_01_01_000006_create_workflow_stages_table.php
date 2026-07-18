<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-16, FR-18 — configurable, ordered workflow stages per scholarship
 * (e.g. Screening -> Committee Review -> Final Approval) with an
 * assigned reviewer role or named user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained('scholarships');
            $table->string('name');
            $table->unsignedTinyInteger('stage_order');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users');
            $table->string('assigned_role')->nullable(); // fallback: role-based assignment
            $table->timestamps();

            $table->unique(['scholarship_id', 'stage_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_stages');
    }
};
