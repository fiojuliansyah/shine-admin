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
        Schema::table('profiles', function (Blueprint $table) {
            $table->enum('last_education',['SD', 'SMP', 'SMA/SLTA', 'D3', 'S1', 'S2', 'S3'])->nullable()->after('gender');
            $table->enum('living_with',['Parent', 'Spouse', 'Family', 'Live Alone'])->default('Live Alone')->after('last_education');
            $table->string('family_name')->nullable()->after('living_with');
            $table->float('height')->nullable()->after('npwp_number');
            $table->float('weight')->nullable()->after('height');
            $table->enum('eye_condition',['Normal', 'Color Blind'])->default('Normal')->after('weight');
            $table->enum('sense',['Normal', 'Poor'])->default('Normal')->after('eye_condition');
            $table->enum('tattoo',['None', 'Present'])->default('None')->after('sense');
            $table->enum('hearing',['Normal', 'Impaired'])->default('Normal')->after('tattoo');
            $table->enum('piercing',['None', 'Present'])->default('None')->after('hearing');
            $table->integer('push_up')->nullable()->after('piercing');
            $table->boolean('pbb',['Normal', 'Abnormal'])->default(false)->after('push_up');
            $table->enum('gada_pratama',['yes', 'no', 'expired'])->default('no')->after('pbb');
            $table->enum('gada_madya',['yes', 'no', 'expired'])->default('no')->after('gada_pratama');
            $table->enum('gada_utama',['yes', 'no', 'expired'])->default('no')->after('gada_madya');
            $table->string('skills')->nullable()->after('gada_utama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            //
        });
    }
};
