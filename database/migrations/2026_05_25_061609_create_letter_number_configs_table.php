<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_number_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('format');
            $table->string('prefix')->nullable();
            $table->integer('padding')->default(3);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_number_configs');
    }
};
