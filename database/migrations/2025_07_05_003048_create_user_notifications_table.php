<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->nullable();
            $table->boolean('job_portal_email')->default(false);
            $table->boolean('job_portal_sms')->default(false);
            $table->boolean('job_portal_whatsapp')->default(false);
            $table->boolean('job_portal_push')->default(false);

            $table->boolean('attendance_email')->default(false);
            $table->boolean('attendance_sms')->default(false);
            $table->boolean('attendance_whatsapp')->default(false);
            $table->boolean('attendance_push')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
