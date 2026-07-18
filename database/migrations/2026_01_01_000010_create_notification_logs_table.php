<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-40 to FR-44 — Status Notifications (NOT YET IMPLEMENTED beyond schema).
 * Table scaffolded to match the ERD. Real email dispatch, templates, and
 * the notification log write-path are planned for Capstone Part 2.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('trigger_event');
            $table->string('channel')->default('email');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
