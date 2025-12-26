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
        Schema::create('business_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->unsignedBigInteger('approved_id')->nullable();

            $table->string('title');
            $table->string('purpose');
            $table->text('information');
            $table->enum('status', ['pending', 'approved', 'in_progress', 'completed'])->default('pending');
            $table->json('images')->nullable(); // akan menampung banyak image dalam format JSON
            $table->timestamps();
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trips');
    }
};
