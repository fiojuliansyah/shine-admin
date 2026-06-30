<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tunj_jabatan', 15, 2)->default(0);
            $table->decimal('tunj_kehadiran', 15, 2)->default(0);
            $table->decimal('tunj_komunikasi', 15, 2)->default(0);
            $table->decimal('tunj_makan', 15, 2)->default(0);
            $table->decimal('tunj_transport', 15, 2)->default(0);
            $table->decimal('tunj_lembur_tetap', 15, 2)->default(0);
            $table->decimal('tunj_other_non_fix', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_settings');
    }
};
