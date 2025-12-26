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
        Schema::create('type_leaves', function (Blueprint $table) {
            $table->id();
            $table->string('site_id')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('is_paid')->nullable();
            $table->string('total')->nullable();
            $table->string('max_per_month')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_leaves');
    }
};
