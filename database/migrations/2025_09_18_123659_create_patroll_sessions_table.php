<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patroll_sessions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')
                  ->constrained('users')
                   ->cascadeOnDelete();
            
            $table->string('patroll_code')->unique();
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('turn')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // sebelum drop table, pastikan foreign key di security_patrolls dihapus dulu
        if (Schema::hasTable('security_patrolls') &&
            Schema::hasColumn('security_patrolls', 'patroll_session_id')) {
            Schema::table('security_patrolls', function (Blueprint $table) {
                $table->dropForeign(['patroll_session_id']);
                // tidak drop kolomnya, cukup drop foreign key supaya bisa drop tabel patroll_sessions
            });
        }

        Schema::dropIfExists('patroll_sessions');
    }
};