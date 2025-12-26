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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->enum('pay_type', ['monthly', 'daily']);
            $table->integer('amount')->nullable();
            $table->integer('cutoff_day')->default(20);
            $table->enum('bpjs_type', ['normatif', 'unnormatif']);
            $table->enum('bpjs_base_type', ['amount_salary', 'salary_allowance', 'base_budget'])->default('amount_salary');
            $table->integer('bpjs_budget_tk')->nullable();
            $table->integer('bpjs_budget_kes')->nullable();
            $table->float('jkk_company')->nullable();
            $table->float('jkm_company')->nullable();
            $table->float('jht_company')->nullable();
            $table->float('jht_employee')->nullable();
            $table->float('jp_company')->nullable();
            $table->float('jp_employee')->nullable();
            $table->float('kes_company')->nullable();
            $table->float('kes_employee')->nullable();
            
            $table->enum('pph21_method', ['ter_gross', 'ter_gross_up'])->default('ter_gross');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
