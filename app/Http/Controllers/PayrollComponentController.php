<?php

namespace App\Http\Controllers;

use App\Models\PayrollComponent;
use Illuminate\Http\Request;

class PayrollComponentController extends Controller
{
    // Create
    public function store(Request $request)
    {
        $request->validate([
            'payroll_id' => 'nullable',
            'pay_type' => 'nullable',
            'component_type' => 'nullable',
            'amount' => 'nullable',
            'percentage' => 'nullable',
            'is_prorate' => 'nullable',
        ]);

        $payrollComponent = PayrollComponent::create([
            'payroll_id' => $request->payroll_id,
            'component_type' => $request->component_type,
            'pay_type' => $request->pay_type,
            'amount' => $request->amount,
            'percentage' => $request->percentage,
            'is_prorate' => $request->is_prorate,
        ]);

        // Redirect after success
        return redirect()->back()->with('success', 'Payroll component created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'payroll_id' => 'nullable',
            'component_type' => 'nullable',
            'pay_type' => 'nullable',
            'name' => 'nullable',
            'amount' => 'nullable',
            'percentage' => 'nullable',
            'is_prorate' => 'nullable',
        ]);

        $payrollComponent = PayrollComponent::find($id);

        if (!$payrollComponent) {
            return redirect()->back()->with('error', 'Payroll component not found');
        }

        $payrollComponent->update([
            'payroll_id' => $request->payroll_id,
            'component_type' => $request->component_type,
            'pay_type' => $request->pay_type,
            'name' => $request->name,
            'amount' => $request->amount,
            'percentage' => $request->percentage,
            'is_prorate' => $request->is_prorate,
        ]);

        return redirect()->back()->with('success', 'Payroll component updated successfully');
    }

    public function destroy($id)
    {
        $payrollComponent = PayrollComponent::find($id);

        if (!$payrollComponent) {
            return redirect()->back()->with('error', 'Payroll component not found');
        }

        $payrollComponent->delete();

        // Redirect after delete
        return redirect()->back()->with('success', 'Payroll component deleted successfully');
    }
}
