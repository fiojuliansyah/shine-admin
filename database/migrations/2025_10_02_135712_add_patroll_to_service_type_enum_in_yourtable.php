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
        Schema::table('jobdesks', function (Blueprint $table) {
             $table->enum('service_type', [
                'cleaning',
                'engineering',
                'security',
                'pest-control',
                'patroll'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobdesks', function (Blueprint $table) {
            $table->enum('service_type', [
                'cleaning',
                'engineering',
                'security',
                'pest-control'
            ])->change();
        });
    }
};
