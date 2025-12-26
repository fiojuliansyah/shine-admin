<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Exports\ActiveUserExport;
use App\Exports\InactiveUserExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SiteAttendanceExport;
use App\Exports\EmployeeAttendanceExport;

class ReportController extends Controller
{
    public function attendanceReport()
    {
        $employees = User::all();
        $sites = Site::all();
        return view('reports.attendances', compact('employees', 'sites'));
    }

    public function employeeExport(Request $request)
    {
        $userId = $request->input('user_id');
        $siteId = $request->input('site_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        return Excel::download(new EmployeeAttendanceExport($userId, $siteId, $startDate, $endDate), 'attendance_report.xlsx');
    }

    public function siteView(Request $request)
    {
        $site_id = $request->input('site_id');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        
        $users = User::where('site_id', $site_id)->get();
        
        $attendances = Attendance::where('site_id', $site_id)
                                ->whereBetween('date', [$start_date, $end_date])
                                ->with(['user', 'overtimes', 'leave'])
                                ->get();
        
        $attendancesByUser = $attendances->groupBy('user_id')->map(function($userAttendances) {
            return $userAttendances->keyBy(function($attendance) {
                return $attendance->date->format('Y-m-d');
            });
        });
        
        $totalsByUser = [];
        
        foreach ($users as $user) {
            $userAttendances = $attendancesByUser->get($user->id, collect());
            
            $totalHK = 0;
            $totalOvertimeMinutes = 0;
            $totalBA = 0;
            $totalLeave = 0;
            $totalShiftOff = 0;
        
            foreach ($userAttendances as $attendance) {
                if ($attendance->type !== 'shift_off' && $attendance->leave_id === null) {
                    $totalHK++;
                }
    
                if ($attendance->type === 'shift_off') {
                    $totalShiftOff++;
                }
        
                foreach ($attendance->overtimes as $overtime) {
                    $overtimeStart = \Carbon\Carbon::parse($overtime->clock_in);
                    $overtimeEnd = \Carbon\Carbon::parse($overtime->clock_out);
        
                    if ($overtimeEnd && $overtimeStart) {
                        $totalOvertimeMinutes += $overtimeStart->diffInMinutes($overtimeEnd);
                    }
                }
        
                if ($attendance->type === 'berita_acara') {
                    $totalBA++;
                }
        
                if ($attendance->leave_id !== null) {
                    $totalLeave++;
                }
            }
        
            $totalOvertimeHours = intdiv($totalOvertimeMinutes, 60);
            $remainingMinutes = $totalOvertimeMinutes % 60;
        
            $totalsByUser[$user->id] = [
                'totalHK' => $totalHK,
                'totalOvertime' => sprintf('%d jam %d menit', $totalOvertimeHours, $remainingMinutes),
                'totalBA' => $totalBA,
                'totalLeave' => $totalLeave,
                'totalShiftOff' => $totalShiftOff,
            ];
        }
        
        $dates = collect();
        $currentDate = \Carbon\Carbon::parse($start_date);
        $endDate = \Carbon\Carbon::parse($end_date);
        
        while ($currentDate->lte($endDate)) {
            $dates->push($currentDate->copy());
            $currentDate->addDay();
        }
        
        return view('reports.site', compact('users', 'attendancesByUser', 'site_id', 'start_date', 'end_date', 'dates', 'totalsByUser'));
    }
    
    public function exportToExcel(Request $request)
    {
        $site_id = $request->input('site_id');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
    
        $users = User::where('site_id', $site_id)->get();
        $attendances = Attendance::where('site_id', $site_id)
            ->whereBetween('date', [$start_date, $end_date])
            ->with(['user', 'overtimes', 'leaves'])
            ->get();
    
        $attendancesByUser = $attendances->groupBy('user_id')->map(function ($userAttendances) {
            return $userAttendances->keyBy(function ($attendance) {
                return $attendance->date->format('Y-m-d');
            });
        });
    
        $totalsByUser = [];
    
        foreach ($users as $user) {
            $userAttendances = $attendancesByUser->get($user->id, collect());
    
            $totalHK = 0;
            $totalOvertimeMinutes = 0;
            $totalOvertimeIn = 0;
            $totalOvertimeOut = 0;
            $totalBA = 0;
            $totalLeave = 0;
    
            foreach ($userAttendances as $attendance) {
                if ($attendance->type !== 'shift_off' && $attendance->leave_id === null) {
                    $totalHK++;
                }
    
                foreach ($attendance->overtimes as $overtime) {
                    try {
                        $overtimeStart = Carbon::parse($overtime->clock_in);
                        $overtimeEnd = Carbon::parse($overtime->clock_out);
    
                        if ($overtimeStart && $overtimeEnd) {
                            $totalOvertimeMinutes += $overtimeStart->diffInMinutes($overtimeEnd);
                            $totalOvertimeIn += $overtimeStart->timestamp;
                            $totalOvertimeOut += $overtimeEnd->timestamp;
                        }
                    } catch (\Exception $e) {
                    }
                }
    
                if ($attendance->type === 'berita_acara') {
                    $totalBA++;
                }
    
                if ($attendance->leave_id !== null) {
                    $totalLeave++;
                }
            }
    
            $totalOvertimeHours = intdiv($totalOvertimeMinutes, 60);
            $remainingMinutes = $totalOvertimeMinutes % 60;
    
            $totalsByUser[$user->id] = [
                'totalHK' => $totalHK,
                'totalOvertime' => sprintf('%d jam %d menit', $totalOvertimeHours, $remainingMinutes),
                'totalBA' => $totalBA,
                'totalLeave' => $totalLeave,
            ];
        }
    
        $dates = collect();
        $currentDate = Carbon::parse($start_date);
        $endDate = Carbon::parse($end_date);
    
        while ($currentDate->lte($endDate)) {
            $dates->push($currentDate->copy());
            $currentDate->addDay();
        }
    
        return Excel::download(new SiteAttendanceExport($attendancesByUser, $dates, $totalsByUser), 'attendance_report.xlsx');
    }

    public function exportActiveUser(Request $request)
    {
        $siteId = $request->input('site_id');
        return Excel::download(new ActiveUserExport($siteId), 'active_users.xlsx');
    }

    public function exportInactiveUser(Request $request)
    {
        $siteId = $request->input('site_id');
        return Excel::download(new InactiveUserExport($siteId), 'inactive_users.xlsx');
    }
}
