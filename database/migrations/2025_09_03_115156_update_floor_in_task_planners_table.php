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
        Schema::table('task_planners', function (Blueprint $table) {
            // Hapus kolom floor lama
            $table->dropColumn('floor');

            // Tambahkan floor_id baru sebagai foreign key
            $table->foreignId('floor_id')->nullable()->constrained('floors')->after('site_id')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_planners', function (Blueprint $table) {
             // Hapus floor_id
            $table->dropForeign(['floor_id']);
            $table->dropColumn('floor_id');

            // Kembalikan kolom floor lama
            $table->string('floor')->nullable()->after('site_id');
        });
    }
};
