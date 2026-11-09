<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel mutations menjadi riwayat karier karyawan:
 * mutasi, promosi, demosi, resign, aktif kembali, dan revisi kontrak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->string('type', 20)->default('mutasi')->after('id');
            $table->string('from_value')->nullable()->after('to_id');
            $table->string('to_value')->nullable()->after('from_value');
            $table->unsignedBigInteger('created_by')->nullable()->after('description');
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type']);
            $table->dropColumn(['type', 'from_value', 'to_value', 'created_by']);
        });
    }
};
