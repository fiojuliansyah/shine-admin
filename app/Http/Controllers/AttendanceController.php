<?php

namespace App\Http\Controllers;

use DataTables;
use Carbon\Carbon;
use App\Models\Site;
use App\Models\User;
use App\Models\Schedule;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\DataTables\AttendancesDataTable;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AttendanceController extends Controller
{
    public function index(Request $request, AttendancesDataTable $dataTable)
    {
        $users = User::all();
        $sites = Site::all();
        
        
        $siteId = $request->input('site_id');
        $type = $request->input('type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        
        $dataTable->with([
            'site_id' => $siteId,
            'type' => $type,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
        
        
        $attendanceQuery = Attendance::query();
        
        
        if ($siteId) {
            $attendanceQuery->where('site_id', $siteId);
        }
        
        if ($startDate && $endDate) {
            $attendanceQuery->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $attendanceQuery->whereDate('date', '>=', $startDate);
        } elseif ($endDate) {
            $attendanceQuery->whereDate('date', '<=', $endDate);
        } else {
            
            $attendanceQuery->whereMonth('date', Carbon::now()->month)
                           ->whereYear('date', Carbon::now()->year);
        }
        
        
        $attendanceCounts = $attendanceQuery->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
        
        
        $attendanceStats = [
            'off' => $attendanceCounts['off'] ?? 0,      
            'late' => $attendanceCounts['late'] ?? 0,    
            'alpha' => $attendanceCounts['alpha'] ?? 0,  
            'regular' => $attendanceCounts['regular'] ?? 0, 
            'leave' => $attendanceCounts['leave'] ?? 0,  
            'permit' => $attendanceCounts['permit'] ?? 0 
        ];
        
        
        $totalEmployees = User::count();
        
        
        
        $filters = [
            'site_id' => $siteId,
            'type' => $type,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
        
        return $dataTable->render('attendances.index', compact(
            'users', 
            'sites', 
            'attendanceStats', 
            'totalEmployees',
            'filters'
        ));
    }
    
    public function getAttendanceStats($startDate = null, $endDate = null)
    {
        $query = Attendance::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('date', $startDate);
        } else {
            
            $query->whereDate('date', today());
        }
        
        $attendanceCounts = $query->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
            
        return [
            'off' => $attendanceCounts['off'] ?? 0,
            'late' => $attendanceCounts['late'] ?? 0,
            'alpha' => $attendanceCounts['alpha'] ?? 0,
            'regular' => $attendanceCounts['regular'] ?? 0,
            'leave' => $attendanceCounts['leave'] ?? 0,
            'permit' => $attendanceCounts['permit'] ?? 0
        ];
    }

    public function create()
    {
        return view('attendances.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required',
            'latlong' => 'required',
            'user_id' => 'required',
            'site_id' => 'required',
            'imagein' => 'nullable|image|max:3000',
            'imageout' => 'nullable|image|max:3000',
        ]);
        
        $attendance = new Attendance;
        $attendance->date = $request->date;
        $attendance->latlong = $request->latlong;
        $attendance->user_id = $request->user_id;
        $attendance->site_id = $request->site_id;
        $attendance->clock_in = $request->clock_in;
        $attendance->clock_out = $request->clock_out;

        if ($request->hasFile('imagein')) {
            $cloudinaryImageIn = $request->file('imagein')->storeOnCloudinary('attendances_images');
            $attendance->imagein_url = $cloudinaryImageIn->getSecurePath();
            $attendance->imagein_public_id = $cloudinaryImageIn->getPublicId();
        }

        if ($request->hasFile('imageout')) {
            $cloudinaryImageOut = $request->file('imageout')->storeOnCloudinary('attendances_images');
            $attendance->imageout_url = $cloudinaryImageOut->getSecurePath();
            $attendance->imageout_public_id = $cloudinaryImageOut->getPublicId();
        }

        $attendance->save();

        return redirect()->route('attendances.index')
                         ->with('success', 'Attendance recorded successfully.');
    }

    public function show(Attendance $attendance)
    {
        return view('attendances.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        return view('attendances.edit', compact('attendance'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'date' => 'required',
            'latlong' => 'required',
            'user_id' => 'required',
            'site_id' => 'required',
            'imagein' => 'nullable|image|max:3000',
            'imageout' => 'nullable|image|max:3000',
        ]);

        $attendance->date = $request->date;
        $attendance->latlong = $request->latlong;
        $attendance->user_id = $request->user_id;
        $attendance->site_id = $request->site_id;
        $attendance->clock_in = $request->clock_in;
        $attendance->clock_out = $request->clock_out;

        if ($request->hasFile('imagein')) {
            Cloudinary::destroy($attendance->imagein_public_id);
            $cloudinaryImageIn = $request->file('imagein')->storeOnCloudinary('attendances_images');
            $attendance->imagein_url = $cloudinaryImageIn->getSecurePath();
            $attendance->imagein_public_id = $cloudinaryImageIn->getPublicId();
        }

        if ($request->hasFile('imageout')) {
            Cloudinary::destroy($attendance->imageout_public_id);
            $cloudinaryImageOut = $request->file('imageout')->storeOnCloudinary('attendances_images');
            $attendance->imageout_url = $cloudinaryImageOut->getSecurePath();
            $attendance->imageout_public_id = $cloudinaryImageOut->getPublicId();
        }

        $attendance->save();

        return redirect()->route('attendances.index')
                         ->with('success', 'Attendance updated successfully.');
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        
        
        if ($attendance->imagein_public_id) {
            Cloudinary::destroy($attendance->imagein_public_id);
        }
    
        
        if ($attendance->imageout_public_id) {
            Cloudinary::destroy($attendance->imageout_public_id);
        }
    
        
        $attendance->delete();
    
        
        return redirect()->route('attendances.index')
                         ->with('success', 'Attendance deleted successfully.');
    }

    public function updateAttendanceStatus(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $count = 0;

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateString = $date->toDateString();

            $schedules = Schedule::where('date', $dateString)->get();

            foreach ($schedules as $schedule) {
                $attendance = Attendance::where('user_id', $schedule->user_id)
                    ->where('date', $dateString)
                    ->first();

                if ($schedule->type === 'off') {
                    Attendance::updateOrCreate(
                        ['user_id' => $schedule->user_id, 'date' => $dateString],
                        [
                            'site_id' => $schedule->site_id,
                            'type' => 'off',
                            'status' => 'approved',
                            'clock_in' => null,
                            'clock_out' => null,
                        ]
                    );
                    $count++;
                    continue;
                }

                if (!$attendance || is_null($attendance->clock_in)) {
                    Attendance::updateOrCreate(
                        ['user_id' => $schedule->user_id, 'date' => $dateString],
                        [
                            'site_id' => $schedule->site_id,
                            'type' => 'alpha',
                            'clock_in' => null,
                            'clock_out' => null,
                        ]
                    );
                    $count++;
                }
            }
        }

        return redirect()->back()->with('success', "$count status kehadiran (Alpha/Off) berhasil diperbarui.");
    }
}