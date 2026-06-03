<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_nik_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('format');
            $table->string('prefix')->nullable();
            $table->unsignedSmallInteger('padding')->default(5);
            $table->unsignedInteger('start_number')->default(1);
            $table->unsignedInteger('current_number')->default(0);
            $table->boolean('is_default')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_nik_configs');
    }
};
