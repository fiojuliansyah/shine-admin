<?php

namespace App\Http\Controllers;

use App\Models\PayrollDeduction;
use Illuminate\Http\Request;

class PayrollDeductionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'payroll_id' => 'nullable',
            'name' => 'nullable',
            'pay_type' => 'nullable',
            'deduction_type' => 'nullable',
            'amount' => 'nullable',
            'percentage' => 'nullable',
            'is_prorate' => 'nullable',
        ]);

        $payrollDeduction = PayrollDeduction::create([
            'payroll_id' => $request->payroll_id,
            'deduction_type' => $request->deduction_type,
            'pay_type' => $request->pay_type,
            'name' => $request->name,
            'amount' => $request->amount,
            'percentage' => $request->percentage,
            'is_prorate' => $request->is_prorate,
        ]);

        // Redirect after success
        return redirect()->back()->with('success', 'Payroll deduction created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'payroll_id' => 'nullable',
            'deduction_type' => 'nullable',
            'pay_type' => 'nullable',
            'name' => 'nullable',
            'amount' => 'nullable',
            'percentage' => 'nullable',
            'is_prorate' => 'nullable',
        ]);

        $payrollDeduction = PayrollDeduction::find($id);

        if (!$payrollDeduction) {
            return redirect()->back()->with('error', 'Payroll deduction not found');
        }

        $payrollDeduction->update([
            'payroll_id' => $request->payroll_id,
            'deduction_type' => $request->deduction_type,
            'pay_type' => $request->pay_type,
            'name' => $request->name,
            'amount' => $request->amount,
            'percentage' => $request->percentage,
            'is_prorate' => $request->is_prorate,
        ]);

        return redirect()->back()->with('success', 'Payroll deduction updated successfully');
    }

    public function destroy($id)
    {
        $payrollDeduction = PayrollDeduction::find($id);

        if (!$payrollDeduction) {
            return redirect()->back()->with('error', 'Payroll deduction not found');
        }

        $payrollDeduction->delete();

        // Redirect after delete
        return redirect()->back()->with('success', 'Payroll deduction deleted successfully');
    }
}
