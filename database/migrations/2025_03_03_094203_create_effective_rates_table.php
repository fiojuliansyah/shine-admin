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
        Schema::create('effective_rates', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->biginteger('lower_limit')->nullable();
            $table->biginteger('upper_limit')->nullable();
            $table->float('rate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('effective_rates');
    }
};
