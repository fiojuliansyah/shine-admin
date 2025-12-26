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
        Schema::create('task_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_planner_id')->constrained('task_planners')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->nullable();
            $table->foreignId('site_id')->constrained('sites')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->enum('is_worked', ['worked', 'not_worked'])->default('not_worked');
            $table->text('progress_description')->nullable();
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('image_before_url')->nullable();
            $table->string('image_before_public_id')->nullable();
            $table->string('image_after_url')->nullable();
            $table->string('image_after_public_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_progress');
    }
};
