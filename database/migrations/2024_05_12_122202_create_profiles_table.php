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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('avatar_public_id')->nullable();
            $table->longText('face_id')->nullable();
            $table->longText('face_descriptor')->nullable();
            $table->longtext('esign')->nullable();
            $table->string('gender')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('number_of_children')->nullable();
            $table->string('npwp_number')->nullable();
            $table->string('marriage_status')->nullable();
            $table->text('address')->nullable();
            $table->string('join_date')->nullable();
            $table->string('resign_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
