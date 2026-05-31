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
            $table->string('rt_rw')->nullable()->after('address');
            $table->string('kelurahan')->nullable()->after('rt_rw');
            $table->string('kecamatan')->nullable()->after('kelurahan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['rt_rw', 'kelurahan', 'kecamatan']);
        });
    }
};
