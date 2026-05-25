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
        Schema::table('letters', function (Blueprint $table) {
            $table->string('number_format')->nullable()->after('description')
                  ->comment('Format nomor surat. Token: {no}, {romawi}, {tahun}, {bulan}, {kode_site}, {kode_tipe}');
            $table->string('number_prefix')->nullable()->after('number_format')
                  ->comment('Prefix tetap sebelum nomor urut');
            $table->integer('number_padding')->default(3)->after('number_prefix')
                  ->comment('Jumlah digit nomor urut, misal 3 = 001');
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn(['number_format', 'number_prefix', 'number_padding']);
        });
    }
};
