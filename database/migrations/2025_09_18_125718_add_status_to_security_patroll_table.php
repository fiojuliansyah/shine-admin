<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('security_patrolls', function (Blueprint $table) {
            // tambah kolom status
            $table->enum('status', ['reported', 'not_reported'])
                  ->after('description')
                  ->nullable();

            // tambah kolom foreign key
            $table->foreignId('patroll_session_id')
                  ->after('task_planner_id')
                  ->constrained('patroll_sessions')
                  ->cascadeOnDelete(); // biar otomatis hapus jika parent terhapus
        });
    }

    public function down(): void
    {
        Schema::table('security_patrolls', function (Blueprint $table) {
            // drop foreign key dulu baru drop kolomnya
            if (Schema::hasColumn('security_patrolls', 'patroll_session_id')) {
                $table->dropForeign(['patroll_session_id']);
                $table->dropColumn('patroll_session_id');
            }

            if (Schema::hasColumn('security_patrolls', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
