<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            if (!Schema::hasColumn('sites', 'area')) {
                $table->string('area')->nullable()->after('name');
            }
            if (!Schema::hasColumn('sites', 'client_position')) {
                $table->string('client_position')->nullable()->after('client_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            if (Schema::hasColumn('sites', 'area')) {
                $table->dropColumn('area');
            }
        });
    }
};
