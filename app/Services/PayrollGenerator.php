<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Payroll;
use App\Models\Overtime;
use App\Models\Attendance;
use App\Models\EffectiveRate;
use App\Models\GeneratePayroll;
use App\Models\PayrollOvertime;
use Illuminate\Support\Facades\DB;
use App\Models\PayrollTimeDeduction;

class PayrollGenerator
{
    public function generate(User $user, $start_date, $end_date)
    {
        return DB::transaction(function () use ($user, $start_date, $end_date) {
            $payroll = $user->payroll;
            $startDate = Carbon::parse($start_date);
            $endDate = Carbon::parse($end_date);

            if (!$payroll || $payroll->amount <= 0) {
                throw new \Exception('Data gaji tidak valid.');
            }

            $salary = $this->calculateSalary($user, $payroll, $startDate, $endDate);

            $allowanceFix = $this->getComponentSum($payroll, 'payroll_components', 'monthly');
            $allowanceNonFix = $this->getComponentSum($payroll, 'payroll_components', 'daily');
            $deductionFix = $this->getComponentSum($payroll, 'payroll_deductions', 'monthly');
            $deductionNonFix = $this->getComponentSum($payroll, 'payroll_deductions', 'daily');

            $timeDeductions = $this->processTimeDeductions($user, $startDate, $endDate);
            $overtimeAmount = $this->processOvertime($user, $startDate, $endDate);

            $bpjs = $this->calculateBPJS($payroll, $salary);
            $pph21 = $this->calculatePPH21($user, $payroll, $salary, $allowanceFix, $allowanceNonFix);

            $totalDeductions = $deductionFix + 
                               $deductionNonFix + 
                               $bpjs['jht_employee'] + 
                               $bpjs['jp_employee'] + 
                               $bpjs['kes_employee'] + 
                               $pph21 + 
                               $timeDeductions['total'];

            $takeHomePay = ($salary + $allowanceFix + $allowanceNonFix + $overtimeAmount) - $totalDeductions;

            return GeneratePayroll::create(array_merge([
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
                'overtime_amount' => $overtimeAmount,
                'pph21' => $pph21,
                'pph21_monthly' => $pph21,
                'take_home_pay' => $takeHomePay,
            ], $bpjs, $timeDeductions['details']));
        });
    }

    private function calculateSalary($user, $payroll, $startDate, $endDate)
    {
        if ($payroll->pay_type === 'daily') {
            $attendanceCount = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->count();
            return $payroll->amount * $attendanceCount;
        }

        $daysInCycle = $startDate->diffInDays($endDate) + 1;
        
        $denominator = $payroll->cutoff_day ? $daysInCycle : $startDate->daysInMonth;
        
        return round(($payroll->amount / $denominator) * $daysInCycle);
    }

    private function getComponentSum($payroll, $relation, $payType, Carbon $startDate, Carbon $endDate)
    {
        $components = $payroll->$relation()->where('pay_type', $payType)->get();
        $total = 0;

        $daysInCycle = $startDate->diffInDays($endDate) + 1;

        foreach ($components as $component) {

            if ($component->expired_at) {
                $expiredDate = Carbon::parse($component->expired_at);
                
                if ($expiredDate->isBefore($startDate)) {
                    continue;
                }

                if ($expiredDate->isBetween($startDate, $endDate)) {
                    $activeDays = $startDate->diffInDays($expiredDate) + 1;
                    $amount = ($component->amount / $daysInCycle) * $activeDays;
                    $total += $amount;
                    continue;
                }
            }
            $amount = $component->amount;

            if ($payType === 'monthly') {
                $amount = ($amount / $daysInCycle) * $daysInCycle; 
            }

            $total += $amount;
        }
        
        return round($total);
    }

    private function processTimeDeductions(User $user, Carbon $startDate, Carbon $endDate)
    {
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy('type');

        $configs = PayrollTimeDeduction::where('user_id', $user->id)->get()->keyBy('type');

        $late = $this->calculateLateSum($attendances->get('late'), $configs->get('late'));
        $alpha = ($attendances->get('alpha')?->count() ?? 0) * ($configs->get('alpha')?->amount ?? 0);
        $permit = ($attendances->get('permit')?->count() ?? 0) * ($configs->get('permit')?->amount ?? 0);
        $leave = ($attendances->get('leave')?->count() ?? 0) * ($configs->get('leave')?->amount ?? 0);

        return [
            'details' => [
                'late_time_deduction' => $late,
                'alpha_time_deduction' => $alpha,
                'permit_time_deduction' => $permit,
                'leave_time_deduction' => $leave,
            ],
            'total' => $late + $alpha + $permit + $leave
        ];
    }

    private function calculateLateSum($entries, $config)
    {
        if (!$entries || !$config) return 0;
        $total = 0;
        foreach ($entries as $entry) {
            if ($entry->late_duration) {
                $time = Carbon::createFromFormat('H:i:s', $entry->late_duration);
                if (($time->hour + ($time->minute / 60)) >= 1) {
                    $total += $config->amount;
                }
            }
        }
        return $total;
    }

    private function processOvertime(User $user, Carbon $startDate, Carbon $endDate)
    {
        $config = PayrollOvertime::where('payroll_id', $user->payroll->id)->first();
        if (!$config || !$config->amount) return 0;

        $attendances = Attendance::with(['overtime' => fn($q) => $q->where('status', 'approved')])
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereNotNull('has_overtime')
            ->get();

        $total = 0;
        foreach ($attendances as $item) {
            if ($config->pay_type === 'hourly' && $item->overtime) {
                $hours = Carbon::parse($item->overtime->clock_out)->diffInHours(Carbon::parse($item->overtime->clock_in));
                $total += $hours * $config->amount;
            } elseif ($config->pay_type === 'daily') {
                $total += $config->amount;
            }
        }
        return $total;
    }

    private function calculateBPJS($payroll, $salary)
    {
        $base = $payroll->amount;
        if ($payroll->bpjs_base_type === 'salary_allowance') {
            $base += $this->getComponentSum($payroll, 'payroll_components', 'monthly');
        }

        $isBudget = $payroll->bpjs_base_type === 'base_budget';
        $tkBase = $isBudget ? $payroll->bpjs_budget_tk : min($base, 9000000);
        $kesBase = $isBudget ? $payroll->bpjs_budget_kes : min($base, 9000000);

        return [
            'jkk_company' => $tkBase * ($payroll->jkk_company / 100),
            'jkm_company' => $tkBase * ($payroll->jkm_company / 100),
            'jht_company' => $tkBase * ($payroll->jht_company / 100),
            'jht_employee' => $tkBase * ($payroll->jht_employee / 100),
            'jp_company' => $tkBase * ($payroll->jp_company / 100),
            'jp_employee' => $tkBase * ($payroll->jp_employee / 100),
            'kes_company' => $kesBase * ($payroll->kes_company / 100),
            'kes_employee' => $kesBase * ($payroll->kes_employee / 100),
        ];
    }

    private function calculatePPH21($user, $payroll, $salary, $allowanceFix, $allowanceNonFix)
    {
        $gross = $salary + $allowanceFix + $allowanceNonFix;
        $status = $user->profile->marriage_status ?? 'TK-0';

        return match ($payroll->pph21_method) {
            'ter_gross' => $this->getTerTax($gross, $status),
            'ter_gross_up' => $this->getTerGrossUpTax($gross, $status),
            default => 0,
        };
    }

    private function getTerTax($income, $status)
    {
        $category = $this->getTerCategory($status);
        $rate = EffectiveRate::where('category', $category)
            ->where('lower_limit', '<=', $income)
            ->where(function ($q) use ($income) {
                $q->where('upper_limit', '>=', $income)->orWhereNull('upper_limit');
            })->first();

        return $rate ? $income * ($rate->rate / 100) : 0;
    }

    private function getTerGrossUpTax($income, $status)
    {
        $grossUp = $income;
        $diff = 1;
        $tax = 0;

        while ($diff > 0.01) {
            $tax = $this->getTerTax($grossUp, $status);
            $newGross = $income + $tax;
            $diff = abs($newGross - $grossUp);
            $grossUp = $newGross;
        }
        return $tax;
    }

    private function getTerCategory($status)
    {
        $map = [
            'TK-0' => 'TER A', 'TK-1' => 'TER A', 'K-0' => 'TER A',
            'TK-2' => 'TER B', 'K-1' => 'TER B', 'TK-3' => 'TER B', 'K-2' => 'TER B',
            'K-3' => 'TER C'
        ];
        return $map[$status] ?? 'TER A';
    }
}