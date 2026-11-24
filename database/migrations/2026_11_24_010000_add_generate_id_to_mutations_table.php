<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Surat terbit (generates) yang diterbitkan untuk proses karier (mutasi/promosi/demosi/resign). */
    public function up(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->unsignedBigInteger('generate_id')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->dropIndex(['generate_id']);
            $table->dropColumn('generate_id');
        });
    }
};
