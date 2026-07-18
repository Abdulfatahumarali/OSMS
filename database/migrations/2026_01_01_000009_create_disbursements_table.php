<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-33 to FR-39 — Disbursement Tracking (NOT YET IMPLEMENTED beyond schema).
 * Table is scaffolded so the schema matches the ERD/Deliverable 2, but no
 * controller, business logic, or tests exist yet for this feature. Planned
 * for Capstone Part 2.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->date('payment_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
