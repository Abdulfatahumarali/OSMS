<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-11 to FR-14 — automated eligibility evaluation, per-criterion
 * pass/fail log, and administrator override with justification.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligibility_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->unique();
            $table->enum('result', ['eligible', 'ineligible'])->nullable();
            // FR-13: JSON log of {criterion => passed:boolean} pairs
            $table->json('failed_criteria')->nullable();
            $table->boolean('is_overridden')->default(false);
            $table->foreignId('overridden_by')->nullable()->constrained('users');
            $table->text('override_justification')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibility_checks');
    }
};
