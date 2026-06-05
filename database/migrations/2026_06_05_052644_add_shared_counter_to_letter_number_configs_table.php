<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_number_configs', function (Blueprint $table) {
            $table->integer('current_number')->nullable()->after('start_number');
            $table->unsignedBigInteger('shared_counter_id')->nullable()->after('current_number');
            $table->foreign('shared_counter_id')->references('id')->on('letter_number_configs')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('letter_number_configs', function (Blueprint $table) {
            $table->dropForeign(['shared_counter_id']);
            $table->dropColumn(['current_number', 'shared_counter_id']);
        });
    }
};
