<?php

namespace App\Http\Controllers;

use PDF;
use DataTables;
use Carbon\Carbon;
use App\Models\Site;
use App\Models\User;
use App\Models\Payroll;
use Illuminate\Http\Request;
use App\Models\ComponentType;
use App\Models\DeductionType;
use App\Models\GeneratePayroll;
use App\Models\PayrollOvertime;
use App\Models\PayrollComponent;
use App\Models\PayrollDeduction;
use Illuminate\Support\Facades\DB;
use App\Models\PayrollTimeDeduction;
use App\DataTables\PayrollsDataTable;
use App\DataTables\PayrollGenerateDataTable;

class PayrollController extends Controller
{
    public function main(PayrollsDataTable $dataTable)
    {
        return $dataTable->render('payrolls.main');
    }

    public function detailPayroll($siteId)
    {
        $site = Site::findOrFail($siteId);
        $componentTypes = ComponentType::where('site_id', $siteId)->get();
        $deductionTypes = DeductionType::where('site_id', $siteId)->get();
        $payrolls = Payroll::with([
            'payroll_components', 
            'payroll_deductions', 
            'payroll_time_deductions', 
            'payroll_overtime', // Add this relation
            'user'
        ])
            ->where('site_id', $siteId)
            ->get();
    
        $componentsData = [];
        $deductionsData = [];
        $timeDeductionsData = [];
        $overtimeData = []; // Create a new array for overtime data
    
        foreach ($payrolls as $payroll) {
            $componentsData[$payroll->id] = [];
            $deductionsData[$payroll->id] = [];
            $timeDeductionsData[$payroll->id] = [
                'late' => 0,
                'alpha' => 0,
                'permit' => 0, // Add default values for permit
                'leave' => 0   // Add default values for leave
            ];
            
            // Load overtime data
            $overtime = $payroll->payroll_overtime;
            $overtimeData[$payroll->id] = [
                'pay_type' => $overtime ? $overtime->pay_type : null,
                'amount' => $overtime ? $overtime->amount : null
            ];
    
            // Load payroll components
            foreach ($componentTypes as $componentType) {
                $component = $payroll->payroll_components->where('component_type_id', $componentType->id)->first();
                $componentsData[$payroll->id][$componentType->id] = $component ? $component->amount : null;
            }
    
            // Load payroll deductions
            foreach ($deductionTypes as $deductionType) {
                $deduction = $payroll->payroll_deductions->where('deduction_type_id', $deductionType->id)->first();
                $deductionsData[$payroll->id][$deductionType->id] = $deduction ? $deduction->amount : null;
            }
    
            // Load payroll time deductions
            foreach ($payroll->payroll_time_deductions as $timeDeduction) {
                $timeDeductionsData[$payroll->id][$timeDeduction->type] = $timeDeduction->amount;
            }
        }
    
        return view('payrolls.detail', [
            'site' => $site,
            'payrolls' => $payrolls,
            'componentTypes' => $componentTypes,
            'deductionTypes' => $deductionTypes,
            'componentsData' => $componentsData,
            'deductionsData' => $deductionsData,
            'timeDeductionsData' => $timeDeductionsData,
            'overtimeData' => $overtimeData, // Pass overtime data to the view
        ]);
    }
    

    public function updatePayroll(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'pay_type' => 'required|in:monthly,daily',
            'amount' => 'nullable|integer|min:0',
        ]);

        $siteId = $request->input('site_id');
        $payType = $request->input('pay_type');

        $users = User::where('site_id', $siteId)->get();

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada user dengan site_id yang sesuai.');
        }

        foreach ($users as $user) {
            $dataToUpdate = [
                'user_id' => $user->id,
                'site_id' => $siteId,
                'pay_type' => $payType,
                'amount' => $request->amount
            ];

            $payroll = Payroll::where('user_id', $user->id)
                            ->where('site_id', $siteId)
                            ->first();

            if ($payroll) {
                $payroll->update($dataToUpdate);
            } else {
                Payroll::create($dataToUpdate);
            }
        }

        return redirect()->back()->with('success', 'Payroll berhasil diperbarui!');
    }

    public function update(Request $request, $id)
    {
        $payroll = Payroll::findOrFail($id);
        
        $request->validate([
            'pay_type' => 'required|in:monthly,daily',
            'amount' => 'nullable|integer|min:0',
            'bpjs_type' => 'required|in:normatif,unnormatif',
        ]);
        
        $payroll->pay_type = $request->pay_type;
        $payroll->amount = $request->amount;
        $payroll->bpjs_type = $request->bpjs_type;
        $payroll->bpjs_base_type = $request->bpjs_base_type;
        $payroll->pph21_method = $request->pph21_method;
    
        // Reset nilai BPJS
        $payroll->jkk_company = 0;
        $payroll->jkm_company = 0;
        $payroll->jht_company = 0;
        $payroll->jht_employee = 0;
        $payroll->jp_company = 0;
        $payroll->jp_employee = 0;
        $payroll->kes_company = 0;
        $payroll->kes_employee = 0;
    
        if ($payroll->bpjs_base_type === 'base_budget') {
            $payroll->bpjs_budget_tk = $request->bpjs_budget_tk ?? 0;
            $payroll->bpjs_budget_kes = $request->bpjs_budget_kes ?? 0;
        } else {
            $payroll->bpjs_budget_tk = null;
            $payroll->bpjs_budget_kes = null;
        }
    
        if ($request->bpjs_type === 'normatif') {
            $selectedBPJS = $request->input('bpjs_normatif', []);
            
            if (in_array('jkk', $selectedBPJS)) {
                $payroll->jkk_company = 0.24;
            }
            
            if (in_array('jkm', $selectedBPJS)) {
                $payroll->jkm_company = 0.30;
            }
            
            if (in_array('jht', $selectedBPJS)) {
                $payroll->jht_company = 3.70;
                $payroll->jht_employee = 2.00;
            }
            
            if (in_array('jp', $selectedBPJS)) {
                $payroll->jp_company = 2.00;
                $payroll->jp_employee = 1.00;
            }
            
            if (in_array('kes', $selectedBPJS)) {
                $payroll->kes_company = 4.00;
                $payroll->kes_employee = 1.00;
            }
        } else {
            $payroll->jkk_company = $request->jkk_company;
            $payroll->jkm_company = $request->jkm_company;
            $payroll->jht_company = $request->jht_company;
            $payroll->jht_employee = $request->jht_employee;
            $payroll->jp_company = $request->jp_company;
            $payroll->jp_employee = $request->jp_employee;
            $payroll->kes_company = $request->kes_company;
            $payroll->kes_employee = $request->kes_employee;
        }
    
        $payroll->save();
    
        PayrollComponent::where('payroll_id', $payroll->id)->delete();
        foreach ($request->input('selected_components', []) as $componentTypeId) {
            $amount = $request->input("component_amounts.{$componentTypeId}", 0);
            PayrollComponent::create([
                'payroll_id' => $payroll->id,
                'component_type_id' => $componentTypeId,
                'pay_type' => $payroll->pay_type,
                'amount' => $amount,
            ]);
        }
    
        PayrollDeduction::where('payroll_id', $payroll->id)->delete();
        foreach ($request->input('selected_deductions', []) as $deductionTypeId) {
            $amount = $request->input("deduction_amounts.{$deductionTypeId}", 0);
            PayrollDeduction::create([
                'payroll_id' => $payroll->id,
                'deduction_type_id' => $deductionTypeId,
                'pay_type' => $payroll->pay_type,
                'amount' => $amount,
            ]);
        }
    
        PayrollTimeDeduction::where('payroll_id', $payroll->id)->delete();
        foreach ($request->input('time_deductions', []) as $timeDeductionType => $amount) {
            PayrollTimeDeduction::create([
                'payroll_id' => $payroll->id,
                'user_id' => $payroll->user->id,
                'type' => $timeDeductionType,
                'amount' => $amount,
            ]);
        }

        $overtimeData = $request->input('overtime');
        if ($overtimeData) {
            PayrollOvertime::updateOrCreate(
                ['payroll_id' => $payroll->id],
                [
                    'pay_type' => $overtimeData['pay_type'],
                    'amount' => $overtimeData['amount'],
                ]
            );
        }
    
        return redirect()->back()->with('success', 'Gaji dan komponen berhasil diperbarui.');
    }    

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'selected_payrolls' => 'required|array',
            'selected_payrolls.*' => 'exists:payrolls,id',
            'pay_type' => 'nullable|in:monthly,daily',
            'pph21_method' => 'nullable|in:ter_gross,ter_gross_up',
            'bpjs_type' => 'nullable|in:normatif,unnormatif',
            'bpjs_base_type' => 'nullable|in:amount_salary,salary_allowance,base_budget',
            'bulk_component_checked' => 'nullable|array',
            'bulk_component_amount' => 'nullable|array',
            'bulk_time_deductions' => 'nullable|array',
            'bulk_overtime' => 'nullable|array',
        ]);
    
        $payrollIds = $request->selected_payrolls;
        $updateCount = 0;
    
        DB::beginTransaction();
    
        try {
            foreach ($payrollIds as $payrollId) {
                $payroll = Payroll::findOrFail($payrollId);
    
                if ($request->filled('pay_type')) {
                    $payroll->pay_type = $request->pay_type;
                    $payroll->amount = $request->amount;
                }
    
                if ($request->filled('pph21_method')) {
                    $payroll->pph21_method = $request->pph21_method;
                }
    
                if ($request->filled('bpjs_base_type')) {
                    $payroll->bpjs_base_type = $request->bpjs_base_type;
                }
    
                if ($payroll->bpjs_base_type === 'base_budget') {
                    $payroll->bpjs_budget_tk = $request->bpjs_budget_tk ?? 0;
                    $payroll->bpjs_budget_kes = $request->bpjs_budget_kes ?? 0;
                } else {
                    $payroll->bpjs_budget_tk = null;
                    $payroll->bpjs_budget_kes = null;
                }
    
                if ($request->filled('bpjs_type')) {
                    $payroll->bpjs_type = $request->bpjs_type;
    
                    $payroll->jkk_company = 0;
                    $payroll->jkm_company = 0;
                    $payroll->jht_company = 0;
                    $payroll->jht_employee = 0;
                    $payroll->jp_company = 0;
                    $payroll->jp_employee = 0;
                    $payroll->kes_company = 0;
                    $payroll->kes_employee = 0;
    
                    if ($request->bpjs_type === 'normatif') {
                        $selectedBPJS = $request->input('bpjs_normatif', []);
    
                        if (in_array('jkk', $selectedBPJS)) {
                            $payroll->jkk_company = 0.24;
                        }
    
                        if (in_array('jkm', $selectedBPJS)) {
                            $payroll->jkm_company = 0.30;
                        }
    
                        if (in_array('jht', $selectedBPJS)) {
                            $payroll->jht_company = 3.70;
                            $payroll->jht_employee = 2.00;
                        }
    
                        if (in_array('jp', $selectedBPJS)) {
                            $payroll->jp_company = 2.00;
                            $payroll->jp_employee = 1.00;
                        }
    
                        if (in_array('kes', $selectedBPJS)) {
                            $payroll->kes_company = 4.00;
                            $payroll->kes_employee = 1.00;
                        }
                    } elseif ($request->bpjs_type === 'unnormatif') {
                        $payroll->jkk_company = $request->input('jkk_company', 0);
                        $payroll->jkm_company = $request->input('jkm_company', 0);
                        $payroll->jht_company = $request->input('jht_company', 0);
                        $payroll->jht_employee = $request->input('jht_employee', 0);
                        $payroll->jp_company = $request->input('jp_company', 0);
                        $payroll->jp_employee = $request->input('jp_employee', 0);
                        $payroll->kes_company = $request->input('kes_company', 0);
                        $payroll->kes_employee = $request->input('kes_employee', 0);
                    }
                }
    
                $payroll->save();
                $updateCount++;
    
                // Update payroll components
                if ($request->has('bulk_component_checked')) {
                    $componentIds = $request->bulk_component_checked;
    
                    foreach ($componentIds as $componentId) {
                        $amount = $request->input("bulk_component_amount.{$componentId}", 0);
    
                        PayrollComponent::updateOrCreate(
                            [
                                'payroll_id' => $payrollId,
                                'component_type_id' => $componentId,
                            ],
                            [
                                'pay_type' => $payroll->pay_type,
                                'amount' => $amount,
                            ]
                        );
                    }
                }
    
                // Update payroll deductions
                if ($request->has('bulk_deduction_checked')) {
                    $deductionIds = $request->bulk_deduction_checked;
    
                    foreach ($deductionIds as $deductionId) {
                        $amount = $request->input("bulk_deduction_amount.{$deductionId}", 0);
    
                        PayrollDeduction::updateOrCreate(
                            [
                                'payroll_id' => $payrollId,
                                'deduction_type_id' => $deductionId,
                            ],
                            [
                                'pay_type' => $payroll->pay_type,
                                'amount' => $amount,
                            ]
                        );
                    }
                }
    
                // Update payroll time deductions ONLY if time deductions data is present
                if ($request->has('bulk_time_deductions') && !empty($request->bulk_time_deductions)) {
                    $timeDeductions = $request->bulk_time_deductions;
    
                    foreach ($timeDeductions as $type => $amount) {
                        if (!empty($amount)) {
                            PayrollTimeDeduction::updateOrCreate(
                                [
                                    'payroll_id' => $payrollId,
                                    'user_id' => $payroll->user->id,
                                    'type' => $type,
                                ],
                                [
                                    'amount' => $amount,
                                ]
                            );
                        }
                    }
                }
    
                // Update payroll overtime ONLY if overtime data is present
                $bulkOvertimeData = $request->input('bulk_overtime');
                if ($bulkOvertimeData && (!empty($bulkOvertimeData['pay_type']) || !empty($bulkOvertimeData['amount']))) {
                    $updateData = [];
                    if (!empty($bulkOvertimeData['pay_type'])) {
                        $updateData['pay_type'] = $bulkOvertimeData['pay_type'];
                    }
                    if (!empty($bulkOvertimeData['amount'])) {
                        $updateData['amount'] = $bulkOvertimeData['amount'];
                    }
                    
                    if (!empty($updateData)) {
                        PayrollOvertime::updateOrCreate(
                            ['payroll_id' => $payrollId],
                            $updateData
                        );
                    }
                }
            }
    
            DB::commit();
            return redirect()->back()->with('success', "{$updateCount} karyawan berhasil diperbarui secara massal.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', "Terjadi kesalahan: {$e->getMessage()}");
        }
    }  

    public function generateIndex(PayrollGenerateDataTable $dataTable)
    {
        $sites = Site::all();
        $period = null;

        $latestPayrolls = GeneratePayroll::select('site_id', DB::raw('MAX(created_at) as latest_created_at'))
            ->groupBy('site_id')
            ->get();

        if ($latestPayrolls->isEmpty()) {
            $totalExpenses = [
                'BPJS Perusahaan' => 0,
                'BPJS Employee' => 0,
                'THP' => 0
            ];
            $latestCreatedAt = "No Data Available";
        } else {
            $latestCreatedAt = $latestPayrolls->max('latest_created_at');
            $expenses = GeneratePayroll::whereIn('created_at', $latestPayrolls->pluck('latest_created_at'))->get();

            $period = $expenses->max('end_date');

            $totalExpenses = [
                'BPJS Perusahaan' => $expenses->sum('jkk_company') + $expenses->sum('jkm_company') + $expenses->sum('jht_company') + $expenses->sum('jp_company') + $expenses->sum('kes_company'),
                'BPJS Employee' => $expenses->sum('jht_employee') + $expenses->sum('jp_employee') + $expenses->sum('kes_employee'),
                'THP' => $expenses->sum('take_home_pay')
            ];
        }

        return $dataTable->render('payrolls.generate', compact('sites', 'latestCreatedAt', 'totalExpenses', 'period'));
    }
    
    public function generateDetail($id, $period)
    {
        $site = Site::findOrFail($id);
        $generatedPayrolls = GeneratePayroll::with('user')
            ->where('site_id', $id)
            ->where('end_date', $period)
            ->get();

        return view('payrolls.generate-detail', compact('site', 'generatedPayrolls', 'period'));
    }  

    public function generate(Request $request)
    {
        $request->validate([
            'site_id' => 'nullable|exists:sites,id', 
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
    
        $start_date = Carbon::createFromFormat('Y-m-d', $request->start_date);
        $end_date = Carbon::createFromFormat('Y-m-d', $request->end_date);
    
        $payrollGenerator = app('payroll.generator');
        $generatedPayrolls = [];
    
        if ($request->site_id) {
            
            $sites = Site::where('id', $request->site_id)->get();
        } else {
            
            $sites = Site::all();
        }
    
        foreach ($sites as $site) {
            
            $existingPayroll = GeneratePayroll::where('site_id', $site->id)
                ->whereDate('start_date', $start_date)
                ->whereDate('end_date', $end_date)
                ->exists(); 
    
            if ($existingPayroll) {
                continue; 
            }
                 
            $users = $site->users;
            foreach ($users as $user) {
                $generatedPayroll = $payrollGenerator->generate($user, $start_date, $end_date);
                $generatedPayrolls[] = $generatedPayroll;
            }
        }
    
        
        if (empty($generatedPayrolls)) {
            return redirect()->back()->with('warning', 'No new payroll generated. Payroll for the same period already exists.');
        }
    
        return redirect()->back()->with('success', 'Payroll generated successfully.');
    }    

    public function viewPayslip($id)
    {
        $payroll = GeneratePayroll::with([
            'user',
            'site',
            'payroll.payroll_components'
        ])->findOrFail($id);

        return view('payrolls.payslip', compact('payroll'));
    }

    public function downloadPayslip($id)
    {
        $payroll = GeneratePayroll::with([
            'user',
            'site',
            'payroll.payroll_components'
        ])->findOrFail($id);

        $pdf = PDF::loadView('payrolls.payslip-pdf', compact('payroll'));

        return $pdf->download('Payslip_'.$payroll->user->name.'_'.\Carbon\Carbon::parse($payroll->end_date)->format('F_Y').'.pdf');
    }


    public function generatePayslip($id, $period)
    {
        $site = Site::findOrFail($id);
        $generatedPayrolls = GeneratePayroll::with('user')
            ->where('site_id', $id)
            ->where('end_date', $period)
            ->get();
    
        if ($generatedPayrolls->isEmpty()) {
            return redirect()->back()->with('error', 'No payroll data found for this period.');
        }
    
        $pdf = PDF::loadView('payrolls.payslip-multi', compact('site', 'generatedPayrolls', 'period'));
    
        return $pdf->download('Payslip_'.$site->name.'_'.\Carbon\Carbon::parse($period)->format('F_Y').'.pdf');
    }


    public function destroy($site_id, $period)
    {
        GeneratePayroll::where('site_id', $site_id)
            ->where('end_date', $period)
            ->delete();

        return redirect()->route('payroll.generate')->with('success', 'Payroll successfully deleted.');
    }
    
}