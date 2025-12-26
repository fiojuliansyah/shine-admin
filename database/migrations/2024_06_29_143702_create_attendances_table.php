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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('latlong')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('face_image_url_clockin')->nullable();
            $table->string('face_image_public_id_clockin')->nullable();
            $table->time('clock_in')->nullable();
            $table->string('face_image_url_clockout')->nullable();
            $table->string('face_image_public_id_clockout')->nullable();
            $table->time('clock_out')->nullable();
            $table->enum('type', ['off', 'late', 'alpha', 'regular', 'leave', 'permit'])->nullable();
            $table->string('has_overtime')->nullable();
            $table->integer('late_duration')->nullable();
            $table->boolean('is_reliver')->nullable();
            $table->foreignId('backup_id')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
