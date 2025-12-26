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
        Schema::create('generate_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('site_id')->nullable();
            $table->foreignId('payroll_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            $table->integer('salary')->nullable();
            $table->integer('allowance_fix')->nullable();
            $table->integer('allowance_non_fix')->nullable();
            $table->integer('deduction_fix')->nullable();
            $table->integer('deduction_non_fix')->nullable();
            $table->integer('late_time_deduction')->nullable();
            $table->integer('alpha_time_deduction')->nullable();
            $table->integer('permit_time_deduction')->nullable();
            $table->integer('leave_time_deduction')->nullable();
            $table->integer('overtime_amount')->nullable();
        
            // BPJS
            $table->float('jkk_company')->nullable();
            $table->float('jkm_company')->nullable();
            $table->float('jht_company')->nullable();
            $table->float('jht_employee')->nullable();
            $table->float('jp_company')->nullable();
            $table->float('jp_employee')->nullable();
            $table->float('kes_company')->nullable();
            $table->float('kes_employee')->nullable();
        
            // Pajak PPh21
            $table->string('pph21')->nullable();
            $table->float('pph21_monthly')->nullable();
        
            $table->string('take_home_pay')->nullable();
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generate_payrolls');
    }
};
