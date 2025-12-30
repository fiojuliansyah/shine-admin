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
        Schema::create('generates', function (Blueprint $table) {
            $table->id();
            $table->string('letter_id')->nullable();
            $table->string('letter_number')->nullable();
            $table->string('romawi')->nullable();
            $table->string('year')->nullable();
            $table->string('day')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('second_party')->nullable();
            $table->longText('second_party_esign')->nullable();
            $table->string('user_id')->nullable();
            $table->string('site_id')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_number')->nullable();
            $table->string('emergency_address')->nullable();
            $table->string('relationship')->nullable();
            $table->longText('description')->nullable();
            $table->longText('esign')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generates');
    }
};
