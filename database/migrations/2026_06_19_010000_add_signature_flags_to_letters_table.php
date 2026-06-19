<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->boolean('require_hrd_signature')->default(false)->after('number_padding');
            $table->boolean('require_employee_signature')->default(false)->after('require_hrd_signature');
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn(['require_hrd_signature', 'require_employee_signature']);
        });
    }
};
