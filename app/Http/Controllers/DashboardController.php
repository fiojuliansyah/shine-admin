<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\User;
use App\Models\Career;
use App\Models\Status;
use App\Models\Company;
use App\Models\Document;
use App\Models\Applicant;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\GeneratePayroll;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        $careerCount = Career::count();
        $attendanceCount = Attendance::count();
        $siteCount = Site::count();
        $companyCount = Company::count();
        $userCount = User::where('is_employee', 1)->count();
        $applicantCount = User::where('is_employee', null)->count();
        
        // Get roles data for the chart
        $roles = Role::withCount('users')->get();
        $rolesData = $roles->map(function($role) {
            return [
                'name' => $role->name,
                'count' => $role->users_count
            ];
        });
        
        // Get all statuses
        $statuses = Status::all();
        $statusData = [];
        $totalApplicants = 0;
        
        // Count applicants for each status
        foreach ($statuses as $status) {
            $count = Applicant::where('status_id', $status->id)
                                ->where('done', null)
                                ->count();
            $statusData[$status->name] = [
                'id' => $status->id,
                'name' => $status->name,
                'count' => $count
            ];
            $totalApplicants += $count;
        }
        
        // Calculate percentages
        foreach ($statusData as $name => $data) {
            $percentage = $totalApplicants > 0 ? round(($data['count'] / $totalApplicants) * 100) : 0;
            $statusData[$name]['percentage'] = $percentage;
        }
        
        // Get attendance statistics
        $today = Carbon::today();
        $presentCount = Attendance::where('type', 'regular')
                        ->whereDate('date', $today)
                        ->count();
        $lateCount = Attendance::where('type', 'late')
                        ->whereDate('date', $today)
                        ->count();
        $permissionCount = Attendance::where('type', 'permit')
                        ->whereDate('date', $today)
                        ->count();
        $absentCount = Attendance::where('type', 'alpha')
                        ->whereDate('date', $today)
                        ->count();
        
        $totalDailyAttendance = $presentCount + $lateCount + $permissionCount + $absentCount;
        
        $attendanceStats = [
            'present_count' => $presentCount,
            'late_count' => $lateCount,
            'permission_count' => $permissionCount,
            'absent_count' => $absentCount,
            'present_percentage' => $totalDailyAttendance > 0 ? round(($presentCount / $totalDailyAttendance) * 100) : 0,
            'late_percentage' => $totalDailyAttendance > 0 ? round(($lateCount / $totalDailyAttendance) * 100) : 0,
            'permission_percentage' => $totalDailyAttendance > 0 ? round(($permissionCount / $totalDailyAttendance) * 100) : 0,
            'absent_percentage' => $totalDailyAttendance > 0 ? round(($absentCount / $totalDailyAttendance) * 100) : 0,
        ];
        
        // Get absentees for today
        $absentees = Attendance::with('user')
                    ->where('type', 'alpha')
                    ->whereDate('date', $today)
                    ->take(10)
                    ->get();
        
        // Get latest attendance records with clock-in and clock-out
        $latestAttendances = Attendance::with('user.roles')
                            ->whereNotNull('clock_in')
                            ->latest('date')
                            ->take(3)
                            ->get();
        
        // Get late attendance records
        $lateAttendances = Attendance::with('user.roles')
                            ->where('type', 'late')
                            ->whereDate('date', $today)
                            ->latest('clock_in')
                            ->take(2)
                            ->get();
        
        // Get top position with most applicants
        $topPosition = Career::select('careers.id', 'careers.name', 'careers.candidate')
        ->leftJoin('applicants', 'applicants.career_id', '=', 'careers.id')
        ->selectRaw('count(distinct applicants.user_id) as applicants_count')
        ->groupBy('careers.id', 'careers.name', 'careers.candidate')
        ->orderBy('applicants_count', 'desc')
        ->first();
        
        $lastMonth = now()->subMonth();
        $startDate = $lastMonth->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $lastMonth->copy()->endOfMonth()->format('Y-m-d');
        
        $companyExpense = GeneratePayroll::whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate])
            ->selectRaw('
                SUM(salary) as total_salary,
                SUM(allowance_fix) as total_allowance_fix,
                SUM(allowance_non_fix) as total_allowance_non_fix,
                SUM(overtime_amount) as total_overtime,
                SUM(jkk_company) as total_jkk_company,
                SUM(jkm_company) as total_jkm_company,
                SUM(jht_company) as total_jht_company,
                SUM(jp_company) as total_jp_company,
                SUM(kes_company) as total_kes_company
            ')
            ->first();
        
        $totalCompanyExpense = 0;
        
        if ($companyExpense) {
            $totalCompanyExpense = (int)$companyExpense->total_salary + 
                                  (int)$companyExpense->total_allowance_fix + 
                                  (int)$companyExpense->total_allowance_non_fix + 
                                  (int)$companyExpense->total_overtime +
                                  (int)$companyExpense->total_jkk_company +
                                  (int)$companyExpense->total_jkm_company +
                                  (int)$companyExpense->total_jht_company +
                                  (int)$companyExpense->total_jp_company +
                                  (int)$companyExpense->total_kes_company;
        }
        
        $lastMonthName = $lastMonth->format('F Y');
        
        $attendances = Attendance::selectRaw('YEAR(date) as year, MONTH(date) as month, COUNT(*) as total')
                            ->groupBy('year', 'month')
                            ->orderBy('year', 'asc')
                            ->orderBy('month', 'asc')
                            ->get();

        $latestJobs = Career::orderBy('created_at', 'desc')
        ->take(4)
        ->get();
                            
        // Get latest applicants
        $latestApplicants = Applicant::with(['user', 'career'])
                            ->orderBy('created_at', 'desc')
                            ->take(4)
                            ->get();

        $recentEmployees = User::where('is_employee', 1)
                      ->with('roles')
                      ->orderBy('created_at', 'desc')
                      ->take(5)
                      ->get();
        
    
        return view('dashboards.dashboard', compact(
            'siteCount', 
            'careerCount', 
            'userCount', 
            'applicantCount',
            'attendances',
            'attendanceCount',
            'companyCount',
            'companyExpense',
            'totalCompanyExpense',
            'lastMonthName',
            'rolesData',
            'statusData',
            'totalApplicants',
            'topPosition',
            'attendanceStats',
            'absentees',
            'latestAttendances',
            'lateAttendances',
            'latestJobs',
            'latestApplicants',
            'recentEmployees'
        ));
    }

    public function recruit()
    {
        $career = Career::count();
        $applicant = Applicant::whereNull('done')
                        ->where('status_id', 0)
                        ->count();
        
        $statuses = Status::all();

        $applicantCounts = [];
        foreach ($statuses as $status) {
            $applicantCounts[$status->id] = Applicant::where('status_id', $status->id)
                                        ->whereNotNull('approve_id')
                                        ->whereNull('done')
                                        ->count();
        }

        return view('dashboards.recruit', compact('statuses', 'applicant', 'applicantCounts', 'career'));
    }

    public function comingsoon()
    {
        return view('dashboards.soon');
    } 
    
    public function activities()
    {
        return view('dashboards.activities');
    } 

    public function welcome()
    {
        return view('landing');
    }

    public function career()
    {
        return view('website.careers.index');
    }
    
    public function careerDetail($id)
    {
        $user = Auth::user();
        $documents = $user ? Document::where('user_id', $user->id)->get() : collect();

        $ID = decrypt($id);
        $career = Career::find($ID);
        return view('website.careers.detail',compact('career','user','documents'));
    }

    public function indexAccount()
    {
        $sites = Site::all();
        $user = Auth::user();
        return view('website.profiles.index',compact('user','sites'));
    }

    public function indexProfile()
    {
        $user = Auth::user();
        return view('website.profiles.profile',compact('user'));
    }

    public function indexDocument()
    {
        $user = Auth::user();
        $documents = Document::where('user_id', $user->id)->get();
        return view('website.profiles.document',compact('user','documents'));
    }
}
