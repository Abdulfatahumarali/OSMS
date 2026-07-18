<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-03, FR-10 — scholarship listing and configurable eligibility criteria.
 * BR-05 — application period enforced via opens_at / closes_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('award_value', 10, 2);
            $table->dateTime('opens_at');
            $table->dateTime('closes_at');

            // FR-10: configurable eligibility criteria stored per scholarship
            $table->decimal('min_gpa', 3, 2)->nullable();
            $table->string('programme_of_study')->nullable();
            $table->unsignedTinyInteger('min_year_of_study')->nullable();
            $table->string('nationality')->nullable();
            $table->boolean('requires_financial_need')->default(false);

            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
