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
        Schema::create('security_patrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('set null');
            $table->foreignId('site_id')->constrained('sites');
            $table->foreignId('task_planner_id')->constrained('task_planners');

            $table->string('name');
            $table->text('description');  
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
        Schema::dropIfExists('security_patrolls');
    }
};
