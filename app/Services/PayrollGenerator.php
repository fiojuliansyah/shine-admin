<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
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

            $allowanceFix = $this->getComponentSum($payroll, 'payroll_components', 'monthly', $startDate, $endDate);
            $allowanceNonFix = $this->getComponentSum($payroll, 'payroll_components', 'daily', $startDate, $endDate);
            $deductionFix = $this->getComponentSum($payroll, 'payroll_deductions', 'monthly', $startDate, $endDate);
            $deductionNonFix = $this->getComponentSum($payroll, 'payroll_deductions', 'daily', $startDate, $endDate);

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
        $validAttendanceTypes = ['regular', 'late', 'permit', 'leave', '', null];

        if ($payroll->pay_type === 'daily') {
            $attendanceCount = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->whereIn('type', $validAttendanceTypes)
                ->count();

            return $payroll->amount * $attendanceCount;
        }

        $divider = (int) ($payroll->cutoff_day ?: 25);

        $workedDays = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('type', $validAttendanceTypes)
            ->count();

        if ($workedDays >= $divider) {
            return $payroll->amount;
        }

        return round(($payroll->amount / $divider) * $workedDays);
    }

    private function getComponentSum($payroll, $relation, $payType, $startDate, $endDate)
    {
        $components = $payroll->$relation()->where('pay_type', $payType)->get();
        $total = 0;
        
        $divider = (int) ($payroll->cutoff_day ?: 25);
        $startDateStr = $startDate->toDateString();
        $endDateStr = $endDate->toDateString();

        foreach ($components as $component) {
            if ($component->expired_at) {
                $expiredDate = Carbon::parse($component->expired_at);
                if ($expiredDate->isBefore($startDate)) continue;
            }

            $amount = $component->amount;

            if ($payType === 'monthly') {
                // 1. LOGIKA: FIX
                if ($component->type === 'fix') {
                    $amount = $component->amount;
                } 
                
                // 2. LOGIKA: ATTENDANCE GUARD (Hangus jika ada 1 pelanggaran)
                elseif ($component->type === 'attendance_guard') {
                    $hasViolation = Attendance::where('user_id', $payroll->user_id)
                        ->whereBetween('date', [$startDateStr, $endDateStr])
                        ->whereIn('type', ['late', 'permit', 'leave', 'alpha'])
                        ->exists();

                    if ($hasViolation) {
                        $amount = 0; // Ada salah satu pelanggaran, tunjangan hangus
                    } else {
                        // Jika bersih, tetap hitung prorata berdasarkan hari kerja regular
                        $workedDays = Attendance::where('user_id', $payroll->user_id)
                            ->whereBetween('date', [$startDateStr, $endDateStr])
                            ->whereIn('type', ['regular', '', null])
                            ->count();
                        
                        if ($workedDays >= $divider) {
                            $amount = $component->amount;
                        } else {
                            $amount = ($component->amount / $divider) * $workedDays;
                        }
                    }
                } 
                
                // 3. LOGIKA: PRORATE (Default)
                else {
                    $workedDays = Attendance::where('user_id', $payroll->user_id)
                        ->whereBetween('date', [$startDateStr, $endDateStr])
                        ->whereIn('type', ['regular', 'late', 'permit', 'leave', '', null])
                        ->count();

                    if ($workedDays >= $divider) {
                        $amount = $component->amount;
                    } else {
                        $amount = ($component->amount / $divider) * $workedDays;
                    }
                }

                // PENGECEKAN EXPIRED (Kecuali jika amount sudah 0 dari guard)
                if ($amount > 0 && isset($expiredDate) && $expiredDate->isBetween($startDate, $endDate)) {
                    $activeDays = $startDate->diffInDays($expiredDate) + 1;
                    $amount = ($amount / $divider) * $activeDays;
                }
            }
            
            $total += $amount;
        }
        return round($total);
    }

    private function processTimeDeductions(User $user, Carbon $startDate, Carbon $endDate)
    {
        $payroll = $user->payroll;
        
        // 1. Cek Data Absen
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $groupedAttendances = $attendances->groupBy(function($item) {
            return trim(strtolower($item->type ?? ''));
        });

        // 2. Cek Data Config (Time Deductions)
        // Kita tarik dulu semua tanpa keyBy untuk melihat isi aslinya di Log
        $rawConfigs = PayrollTimeDeduction::where('payroll_id', $payroll->id)->get();
        
        $configs = $rawConfigs->keyBy(function($item) {
            return trim(strtolower($item->type ?? ''));
        });

        // 3. Kalkulasi
        $lateAmount = $configs->get('late')?->amount ?? 0;
        $late = $this->calculateLateSum($groupedAttendances->get('late'), $lateAmount);
        
        $alphaAmount = (float) ($configs->get('alpha')?->amount ?? 0);
        $alphaCount = $groupedAttendances->get('alpha')?->count() ?? 0;
        $alpha = $alphaCount * $alphaAmount;

        $permitAmount = (float) ($configs->get('permit')?->amount ?? 0);
        $permitCount = $groupedAttendances->get('permit')?->count() ?? 0;
        $permit = $permitCount * $permitAmount;

        $leaveAmount = (float) ($configs->get('leave')?->amount ?? 0);
        $leaveCount = $groupedAttendances->get('leave')?->count() ?? 0;
        $leave = $leaveCount * $leaveAmount;

        // ==========================================
        // LOGGING DEBUGGING (CEK DI LARAVEL.LOG)
        // ==========================================
        \Log::info("=== DEBUG TIME DEDUCTION [{$user->name}] ===");
        \Log::info("Payroll ID: " . $payroll->id);
        \Log::info("Data Absen Ditemukan: " . $attendances->count());
        \Log::info("Tipe Absen di DB: " . implode(', ', $groupedAttendances->keys()->toArray()));
        
        \Log::info("Data Config di DB (Raw): ", $rawConfigs->toArray());
        
        \Log::info("Hasil Mapping Config:", [
            'alpha' => [
                'count_absen' => $alphaCount,
                'config_amount' => $alphaAmount,
                'total_potongan' => $alpha
            ],
            'late' => [
                'count_absen' => $groupedAttendances->get('late')?->count() ?? 0,
                'config_amount' => $lateAmount,
                'total_potongan' => $late
            ]
        ]);
        \Log::info("=== END DEBUG ===");

        return [
            'details' => [
                'late_time_deduction' => (float)$late,
                'alpha_time_deduction' => (float)$alpha,
                'permit_time_deduction' => (float)$permit,
                'leave_time_deduction' => (float)$leave,
            ],
            'total' => (float)($late + $alpha + $permit + $leave)
        ];
    }

    private function calculateLateSum($entries, $amount)
    {
        if (!$entries || $amount <= 0) return 0;
        
        $total = 0;
        foreach ($entries as $entry) {
            // Cek jika duration tidak null, tidak kosong, dan tidak "0"
            // Kita gunakan abs() agar nilai -17 tetap dianggap ada durasi terlambat
            $duration = (int) $entry->late_duration;
            
            if ($duration !== 0) {
                $total += $amount;
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
            $base += $this->getComponentSum($payroll, 'payroll_components', 'monthly', Carbon::now(), Carbon::now());
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