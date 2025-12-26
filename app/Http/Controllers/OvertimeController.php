<?php

namespace App\Http\Controllers;

use DataTables;
use Carbon\Carbon;
use App\Models\Site;
use App\Models\User;
use App\Models\Overtime;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Exports\OvertimeExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\DataTables\OvertimesDataTable;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(OvertimesDataTable $dataTable, Request $request)
    {
        $users = User::all();
        $sites = Site::all();
        $overtimeStatuses = ['pending', 'approved', 'rejected'];
    
        $filters = [
            'user_id' => $request->input('user_id'),
            'site_id' => $request->input('site_id'),
            'status' => $request->input('status'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
    
        // Count queries with appropriate filters
        $rejectedCount = Overtime::where('status', 'rejected')->count();
        $pendingCount = Overtime::where('status', 'pending')->count();
        $overtimeCount = Overtime::count();
    
        return $dataTable->render('attendances.overtimes.index', compact(
            'users',
            'sites',
            'overtimeStatuses',
            'rejectedCount',
            'pendingCount',
            'overtimeCount',
            'filters'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $today = Carbon::now()->format('Y-m-d');
        $userId = Auth::user()->id;

        // Create or update attendance with has_overtime flag
        $attendance = Attendance::firstOrCreate(
            ['date' => $today, 'user_id' => $userId],
            [
                'clock_in' => $request->clock_in, 
                'clock_out' => $request->clock_out,
                'has_overtime' => 'yes' // Set has_overtime flag
            ]
        );
        
        // If the attendance already existed, update the has_overtime flag
        if (!$attendance->wasRecentlyCreated) {
            $attendance->update([
                'has_overtime' => 'yes'
            ]);
        }
    
        // Create or update overtime record
        Overtime::updateOrCreate(
            ['attendance_id' => $attendance->id],
            ['clock_in' => $request->clock_in, 'clock_out' => $request->clock_out]
        );
    
        return redirect()->route('overtimes.index')->with('success', 'Overtime created successfully.');
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Overtime $overtime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Overtime $overtime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Overtime $overtime)
    {
        $attendance = Attendance::find($request->attendance_id);
    
        if ($attendance) {
            $attendance->update([
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
                'has_overtime' => 'yes' // Ensure has_overtime is set
            ]);
        }
    
        $overtime->update([
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
        ]);
    
        return redirect()->route('overtimes.index')->with('success', 'Overtime updated successfully.');
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Overtime $overtime)
    {
        // Get the attendance record to update has_overtime status
        $attendance = Attendance::find($overtime->attendance_id);
        
        if ($attendance) {
            // Remove the has_overtime flag when deleting the overtime record
            $attendance->update([
                'has_overtime' => null
            ]);
        }
        
        $overtime->delete();

        return redirect()->route('overtimes.index')->with('success', 'Overtime deleted successfully.');
    }
    

    public function export(Request $request)
    {
        $site_id = $request->site_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $filename = 'lembur_(' . $start_date . ')-(' . $end_date . ').xlsx';

        return Excel::download(new OvertimeExport($site_id, $start_date, $end_date), $filename);
    }
}