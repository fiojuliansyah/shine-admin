<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('emergency_name')->nullable()->after('religion');
            $table->string('emergency_phone')->nullable()->after('emergency_name');
            $table->string('emergency_relation')->nullable()->after('emergency_phone');
            $table->string('emergency_address')->nullable()->after('emergency_relation');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['emergency_name', 'emergency_phone', 'emergency_relation', 'emergency_address']);
        });
    }
};
