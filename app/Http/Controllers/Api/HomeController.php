<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Leave;
use App\Models\Permit;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\TaskPlanner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
    
        $currentDate = Carbon::now()->toDateString();
        $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();
        $currentMonthEnd = Carbon::now()->endOfMonth()->toDateString();
    
        $attendanceCount = Attendance::where('user_id', $userId)
                        ->whereDate('date', '>=', $currentMonthStart)
                        ->whereDate('date', '<=', $currentMonthEnd)
                        ->count();

        $overtimeCount = Attendance::where('type', 'overtime')
                        ->where('user_id', $userId)
                        ->whereDate('date', '>=', $currentMonthStart)
                        ->whereDate('date', '<=', $currentMonthEnd)
                        ->count();
    
        $lateCount = Attendance::where('type', 'late')
                    ->where('user_id', $userId)
                    ->whereDate('date', '>=', $currentMonthStart)
                    ->whereDate('date', '<=', $currentMonthEnd)
                    ->count();
    
        $alphaCount = Attendance::where('type', 'alpha')
                    ->where('user_id', $userId)
                    ->whereDate('date', '>=', $currentMonthStart)
                    ->whereDate('date', '<=', $currentMonthEnd)
                    ->count();

        $permitCount = Permit::where('user_id', $userId)
                    ->whereDate('created_at', '>=', $currentMonthStart)
                    ->whereDate('created_at', '<=', $currentMonthEnd)
                    ->count();

        $leaveCount = Leave::where('user_id', $userId)
                    ->whereDate('created_at', '>=', $currentMonthStart)
                    ->whereDate('created_at', '<=', $currentMonthEnd)
                    ->count();
        
        $schedule = Schedule::where('user_id', $userId)
                            ->where('date', $currentDate)
                            ->first();
    
        $latestClockIn = Attendance::where('user_id', $userId)
                        ->whereDate('date', $currentDate)
                        ->whereNotNull('clock_in')
                        ->exists();
    
        $latestAttendance = Attendance::where('user_id', $userId)
                        ->where(function ($query) use ($currentDate) {
                            $query->whereDate('date', $currentDate)
                                  ->orWhereNull('clock_out');
                        })
                        ->latest()
                        ->first();
    
        $yesterdayAttendance = Attendance::where('user_id', $userId)
                        ->whereDate('date', Carbon::yesterday()->toDateString())
                        ->latest()
                        ->first();
    
        $currentMonthName = Carbon::now()->format('F Y');

        $tasks = TaskPlanner::whereDate('date', Carbon::now()->toDateString())->get();
    
        return response()->json([
            'user' => Auth::user()->load('profile','roles', 'site', 'sites_leader'),
            'attendanceCount' => $attendanceCount, 
            'overtimeCount' => $overtimeCount, 
            'lateCount' => $lateCount, 
            'alphaCount' => $alphaCount,
            'permitCount' => $permitCount,
            'leaveCount' => $leaveCount, 
            'schedule' => $schedule,
            'latestClockIn' => $latestClockIn, 
            'latestAttendance' => $latestAttendance, 
            'yesterdayAttendance' => $yesterdayAttendance,
            'currentMonthName' => $currentMonthName,
            'tasks' => $tasks
        ]);
    }

    public function logs(Request $request)
    {
        $userId = Auth::id();

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $query = Attendance::with('site')
            ->where('user_id', $userId)
            ->whereNotNull('clock_in')
            ->orderBy('date', 'DESC');

        // pakai kolom `date` bukan `created_at`
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $logs = $query->paginate(5);

        return response()->json($logs);
    }
}
