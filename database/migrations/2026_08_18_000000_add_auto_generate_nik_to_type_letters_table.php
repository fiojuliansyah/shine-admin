<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('type_letters', function (Blueprint $table) {
            $table->boolean('auto_generate_nik')->default(false)->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('type_letters', function (Blueprint $table) {
            $table->dropColumn('auto_generate_nik');
        });
    }
};
