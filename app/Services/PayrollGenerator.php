<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Payroll;
use App\Models\Overtime;
use App\Models\PtkpRate;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\EffectiveRate;
use App\Models\GeneratePayroll;
use App\Models\PayrollOvertime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PayrollTimeDeduction;

class PayrollGenerator
{
    public function generate(User $user, $start_date, $end_date)
    {
        DB::beginTransaction();
    
        try {
            $payroll = $user->payroll;
            $startDate = Carbon::parse($start_date);
            $endDate = Carbon::parse($end_date);
    
            if ($payroll->amount <= 0) {
                throw new \Exception('Gaji pokok tidak boleh nol atau negatif.');
            }
    
            
            if ($payroll->pay_type === 'daily') {

                $attendanceCount = $user->attendances()
                    ->whereBetween('date', [$start_date, $end_date])
                    ->count();

                $salary = $payroll->amount * $attendanceCount;

            } else {
                $daysInMonth = $startDate->daysInMonth;
                $workedDays = $startDate->diffInDays($endDate) + 1;
                $dailyRate = $payroll->amount / $daysInMonth;
                $salary = round($dailyRate * $workedDays);
            }

    
            
            $allowanceFix = $this->calculateAllowance($payroll, 'monthly');
            $allowanceNonFix = $this->calculateAllowance($payroll, 'daily', $startDate, $endDate);
            $deductionFix = $this->calculateDeduction($payroll, 'monthly');
            $deductionNonFix = $this->calculateDeduction($payroll, 'daily', $startDate, $endDate);
    
            
            $bpjs = $this->calculateBPJS($payroll, $salary);
            $pph21Monthly = $this->calculatePPH21($user, $payroll, $salary, $allowanceFix, $allowanceNonFix);
    
            $lateTimeDeduction = $this->calculateLateTimeDeduction($user, $startDate, $endDate);
            $alphaTimeDeduction = $this->calculateAlphaTimeDeduction($user, $startDate, $endDate);
            $permitTimeDeduction = $this->calculatePermitTimeDeduction($user, $startDate, $endDate);
            $leaveTimeDeduction = $this->calculateLeaveTimeDeduction($user, $startDate, $endDate);

            $timeDeductionFix = $lateTimeDeduction + $alphaTimeDeduction + $permitTimeDeduction + $leaveTimeDeduction;
            
            $overtime = $this->calculateOvertime($user, $startDate, $endDate);
    
            $takeHomePay = $salary + $allowanceFix + $allowanceNonFix + $overtime - $deductionFix - $deductionNonFix 
                           - $bpjs['jht_employee'] - $bpjs['jp_employee'] - $bpjs['kes_employee'] 
                           - $pph21Monthly - $timeDeductionFix;
    
            
            GeneratePayroll::create([
                'user_id' => $user->id,
                'site_id' => $payroll->site_id,
                'payroll_id' => $payroll->id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'salary' => $salary,
                'allowance_fix' => $allowanceFix,
                'allowance_non_fix' => $allowanceNonFix,
                'deduction_fix' => $deductionFix,
                'deduction_non_fix' => $deductionNonFix,
                'late_time_deduction' => $lateTimeDeduction,
                'alpha_time_deduction' => $alphaTimeDeduction,
                'permit_time_deduction' => $permitTimeDeduction,
                'leave_time_deduction' => $leaveTimeDeduction,
                'overtime_amount' => $overtime,
                'jkk_company' => $bpjs['jkk_company'],
                'jkm_company' => $bpjs['jkm_company'],
                'jht_company' => $bpjs['jht_company'],
                'jht_employee' => $bpjs['jht_employee'],
                'jp_company' => $bpjs['jp_company'],
                'jp_employee' => $bpjs['jp_employee'],
                'kes_company' => $bpjs['kes_company'],
                'kes_employee' => $bpjs['kes_employee'],
                'pph21' => $pph21Monthly,
                'pph21_monthly' => $pph21Monthly,
                'take_home_pay' => $takeHomePay,
            ]);
    
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    public function calculateLateTimeDeduction(User $user, Carbon $startDate, Carbon $endDate)
    {
        $startDate = $startDate->toDateString();
        $endDate = $endDate->toDateString();
    
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('type', 'late')
            ->whereNotNull('late_duration')
            ->get();
        
        $lateDeduction = PayrollTimeDeduction::where('user_id', $user->id)
                                             ->where('type', 'late')
                                             ->first();
        
        if (!$lateDeduction) {
            return 0;
        }
    
        $totalDeduction = 0;
    
        foreach ($attendances as $entry) {
            if ($entry->late_duration) {

                $lateDuration = Carbon::createFromFormat('H:i:s', $entry->late_duration);

                $lateDurationInHours = $lateDuration->hour + ($lateDuration->minute / 60) + ($lateDuration->second / 3600);
        
                if ($lateDurationInHours >= 1) {
                    $totalDeduction += $lateDeduction->amount;
                }
            } else {
                Log::warning("No late duration found for User ID {$user->id} on Date: {$entry->date}");
            }
        }
            
        return $totalDeduction;
    }
    
    
    public function calculateAlphaTimeDeduction(User $user, Carbon $startDate, Carbon $endDate)
    {
        $startDate = $startDate->toDateString();
        $endDate = $endDate->toDateString();
    
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate]) 
            ->where('type', 'alpha')
            ->get();
        
        $alphaDeduction = PayrollTimeDeduction::where('user_id', $user->id)
                                              ->where('type', 'alpha')
                                              ->first();
        
        if (!$alphaDeduction) {
            return 0;
        }
    
        $alphaDeductionAmount = $attendances->count() * $alphaDeduction->amount;
        
        return $alphaDeductionAmount;
    }
    
    public function calculatePermitTimeDeduction(User $user, Carbon $startDate, Carbon $endDate)
    {
        $startDate = $startDate->toDateString();
        $endDate = $endDate->toDateString();
    
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate]) 
            ->where('type', 'permit')
            ->get();
        
        $permitDeduction = PayrollTimeDeduction::where('user_id', $user->id)
                                              ->where('type', 'permit')
                                              ->first();
        
        if (!$permitDeduction) {
            return 0;
        }
    
        $permitDeductionAmount = $attendances->count() * $permitDeduction->amount;
        
        Log::debug("Permit Deduction for User ID {$user->id}: Count: {$attendances->count()}, Amount: {$permitDeductionAmount}");
        return $permitDeductionAmount;
    }
    
    public function calculateLeaveTimeDeduction(User $user, Carbon $startDate, Carbon $endDate)
    {
        $startDate = $startDate->toDateString();
        $endDate = $endDate->toDateString();
    
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate]) 
            ->where('type', 'leave')
            ->get();
        
        $leaveDeduction = PayrollTimeDeduction::where('user_id', $user->id)
                                             ->where('type', 'leave')
                                             ->first();
        
        if (!$leaveDeduction) {
            return 0;
        }
    
        $leaveDeductionAmount = $attendances->count() * $leaveDeduction->amount;
        
        Log::debug("Leave Deduction for User ID {$user->id}: Count: {$attendances->count()}, Amount: {$leaveDeductionAmount}");
        return $leaveDeductionAmount;
    }
    
    public function calculateOvertime(User $user, Carbon $startDate, Carbon $endDate)
    {
        $startDate = $startDate->toDateString();
        $endDate = $endDate->toDateString();
        
        $payrollOvertime = PayrollOvertime::where('payroll_id', $user->payroll->id)->first();
        
        if (!$payrollOvertime || !$payrollOvertime->amount) {
            return 0;
        }
        
        $totalOvertime = 0;
        
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('has_overtime')
            ->get();
        
        if ($payrollOvertime->pay_type === 'hourly') {
            foreach ($attendances as $attendance) {
                $overtime = Overtime::where('attendance_id', $attendance->id)
                    ->where('status', 'approved')
                    ->whereNotNull('clock_in')
                    ->whereNotNull('clock_out')
                    ->first();
                
                if ($overtime) {
                    $clockIn = Carbon::parse($overtime->clock_in);
                    $clockOut = Carbon::parse($overtime->clock_out);
                    
                    $overtimeHours = $clockOut->diffInHours($clockIn);
                    
                    $totalOvertime += $overtimeHours * $payrollOvertime->amount;
                }
            }
        } else {
            $totalOvertime = $attendances->count() * $payrollOvertime->amount;
        }
        
        Log::debug("Overtime for User ID {$user->id}: Type: {$payrollOvertime->pay_type}, Total: {$totalOvertime}");
        return $totalOvertime;
    }
    
    public function calculateTotalDeductions(User $user, Carbon $startDate, Carbon $endDate)
    {
        $lateDeductionAmount = $this->calculateLateTimeDeduction($user, $startDate, $endDate);
        $alphaDeductionAmount = $this->calculateAlphaTimeDeduction($user, $startDate, $endDate);
        $permitDeductionAmount = $this->calculatePermitTimeDeduction($user, $startDate, $endDate);
        $leaveDeductionAmount = $this->calculateLeaveTimeDeduction($user, $startDate, $endDate);
        
        $totalDeduction = $lateDeductionAmount + $alphaDeductionAmount + $permitDeductionAmount + $leaveDeductionAmount;
        
        Log::debug("Total Deductions for User ID {$user->id}: {$totalDeduction}");
        return $totalDeduction;
    }

    private function calculateAllowance(Payroll $payroll, $payType, Carbon $startDate = null, Carbon $endDate = null)
    {
        $query = $payroll->payroll_components()->where('pay_type', $payType);
    
        if ($payType === 'daily') {
            return $query->sum('amount');
        }
    
        return $query->sum('amount');
    }

    private function calculateDeduction(Payroll $payroll, $payType, Carbon $startDate = null, Carbon $endDate = null)
    {
        $query = $payroll->payroll_deductions()->where('pay_type', $payType);
    
        if ($payType === 'daily') {
            return $query->sum('amount');
        }
    
        return $query->sum('amount');
    }

    private function getWorkingDays(Carbon $startDate, Carbon $endDate)
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        return $period->filter(fn(Carbon $date) => $date->isWeekday())->count();
    }

    private function calculateBPJS(Payroll $payroll, $salary)
    {
        
        $base = $payroll->amount;
    
        
        if ($payroll->bpjs_base_type === 'salary_allowance') {
            $allowance = $payroll->payroll_components()->where('pay_type', 'monthly')->sum('amount');
            $base += $allowance;
        }
    
        
        if ($payroll->bpjs_base_type === 'base_budget') {
            return [
                'jkk_company' => $payroll->bpjs_budget_tk * ($payroll->jkk_company / 100),
                'jkm_company' => $payroll->bpjs_budget_tk * ($payroll->jkm_company / 100),
                'jht_company' => $payroll->bpjs_budget_tk * ($payroll->jht_company / 100),
                'jht_employee' => $payroll->bpjs_budget_tk * ($payroll->jht_employee / 100),
                'jp_company' => $payroll->bpjs_budget_tk * ($payroll->jp_company / 100),
                'jp_employee' => $payroll->bpjs_budget_tk * ($payroll->jp_employee / 100),
                'kes_company' => $payroll->bpjs_budget_kes * ($payroll->kes_company / 100),
                'kes_employee' => $payroll->bpjs_budget_kes * ($payroll->kes_employee / 100),
            ];
        }
    
        
        $bpjsBase = min($base, 9000000);
    
        return [
            'jkk_company' => $bpjsBase * ($payroll->jkk_company / 100),
            'jkm_company' => $bpjsBase * ($payroll->jkm_company / 100),
            'jht_company' => $bpjsBase * ($payroll->jht_company / 100),
            'jht_employee' => $bpjsBase * ($payroll->jht_employee / 100),
            'jp_company' => $bpjsBase * ($payroll->jp_company / 100),
            'jp_employee' => $bpjsBase * ($payroll->jp_employee / 100),
            'kes_company' => $bpjsBase * ($payroll->kes_company / 100),
            'kes_employee' => $bpjsBase * ($payroll->kes_employee / 100),
        ];
    }

    private function calculatePPH21(User $user, Payroll $payroll, $salary, $allowanceFix, $allowanceNonFix)
    {
        $monthlyGrossIncome = $salary + $allowanceFix + $allowanceNonFix;

        switch ($payroll->pph21_method) {
            case 'ter_gross':
                return $this->calculateTERPPH21($monthlyGrossIncome, $user->profile->marriage_status);

            case 'ter_gross_up':
                return $this->calculateTERGrossUpPPH21($monthlyGrossIncome, $user->profile->marriage_status);

            default:
                throw new \Exception("Metode PPh21 tidak valid: {$payroll->pph21_method}");
        }
    }

    private function calculateTERPPH21($monthlyIncome, $marriageStatus)
    {
        $terCategory = $this->getTERCategory($marriageStatus);

        $rate = EffectiveRate::where('category', $terCategory)
            ->where('lower_limit', '<=', $monthlyIncome)
            ->where(function ($query) use ($monthlyIncome) {
                $query->where('upper_limit', '>=', $monthlyIncome)->orWhereNull('upper_limit');
            })
            ->first();

        if (!$rate) {
            throw new \Exception("Rate TER tidak ditemukan untuk penghasilan {$monthlyIncome} dan kategori {$terCategory}");
        }

        $monthlyTax = $monthlyIncome * ($rate->rate / 100);

        return $monthlyTax;
    }

    private function calculateTERGrossUpPPH21($monthlyIncome, $marriageStatus)
    {
        $terCategory = $this->getTERCategory($marriageStatus);

        $difference = 1;
        $grossUpIncome = $monthlyIncome;

        while ($difference > 0.01) {
            $rate = EffectiveRate::where('category', $terCategory)
                ->where('lower_limit', '<=', $grossUpIncome)
                ->where(function ($query) use ($grossUpIncome) {
                    $query->where('upper_limit', '>=', $grossUpIncome)->orWhereNull('upper_limit');
                })
                ->first();

            if (!$rate) {
                throw new \Exception("Rate TER tidak ditemukan untuk penghasilan {$grossUpIncome} dan kategori {$terCategory}");
            }

            $tax = $grossUpIncome * ($rate->rate / 100);
            $newGrossUpIncome = $monthlyIncome + $tax;
            $difference = abs($newGrossUpIncome - $grossUpIncome);
            $grossUpIncome = $newGrossUpIncome;
        }

        return $tax;
    }

    private function getTERCategory($marriageStatus)
    {
        $categories = [
            'TK-0' => 'TER A',
            'TK-1' => 'TER A',
            'K-0' => 'TER A',
            'TK-2' => 'TER B',
            'K-1' => 'TER B',
            'TK-3' => 'TER B',
            'K-2' => 'TER B',
            'K-3' => 'TER C',
        ];

        return $categories[$marriageStatus] ?? 'TER A';
    }
}