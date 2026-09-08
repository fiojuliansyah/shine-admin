<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('employment_status')->default('ACTIVE')->after('user_id');
            $table->string('no_kk')->nullable()->after('npwp_number');
            $table->string('end_date')->nullable()->after('join_date');
            $table->string('mutation_date')->nullable()->after('end_date');
            $table->string('bpjs_tk_number')->nullable()->after('account_number');
            $table->string('bpjs_kes_number')->nullable()->after('bpjs_tk_number');
            $table->text('keterangan')->nullable()->after('bpjs_kes_number');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'employment_status',
                'no_kk',
                'end_date',
                'mutation_date',
                'bpjs_tk_number',
                'bpjs_kes_number',
                'keterangan',
            ]);
        });
    }
};
