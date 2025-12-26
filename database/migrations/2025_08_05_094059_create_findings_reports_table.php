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
        Schema::create('findings_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('task_planner_id')->nullable();

            $table->string('title');
            $table->date('date');
            $table->text('description');
            $table->string('location');
            $table->string('direct_action');

            $table->enum('status', ['pending', 'solved'])->default('pending');
            $table->enum('type', ['low', 'medium', 'high'])->default('low');
            $table->boolean('is_work_assignments')->default(false);

            $table->string('image_url')->nullable();
            $table->string('image_public_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('findings_reports');
    }
};
