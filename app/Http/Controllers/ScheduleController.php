<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Shift;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Imports\ScheduleImport;
use Maatwebsite\Excel\Facades\Excel;
use App\DataTables\SchedulesDataTable;

class ScheduleController extends Controller
{
    public function index(SchedulesDataTable $dataTable)
    {
        return $dataTable->render('schedules.index');
    }

    public function show($siteId)
    {
        $site = Site::findOrFail($siteId);

        $sites = site::all();
        
        $schedules = Schedule::where('site_id', $siteId)->with('user')->get();
        $shifts = Shift::where('site_id', $siteId)->get();
    
        $dates = $schedules->pluck('date')->unique()->sort()->values();
    
        $groupedSchedules = $schedules->groupBy('user_id');
    
        return view('schedules.show', compact('site', 'dates', 'groupedSchedules', 'sites', 'shifts'));
    }
    

    public function shiftStore(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'name' => 'required|string|max:255',
            'shift_code' => 'required|string|max:10',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'type' => 'nullable|in:off,leave',
        ]);
    
        if ($request->clock_in && $request->clock_out) {
            $clockIn = \Carbon\Carbon::createFromFormat('H:i', $request->clock_in);
            $clockOut = \Carbon\Carbon::createFromFormat('H:i', $request->clock_out);
    
            if ($clockOut->lessThan($clockIn)) {
                $clockOut->addDay();
            }
        }
    
        Shift::create([
            'site_id' => $request->site_id,
            'name' => $request->name,
            'shift_code' => $request->shift_code,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'type' => $request->type ?? null,
        ]);
    
        return redirect()->back()->with('success', 'Shift created successfully.');
    }
    

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
            'month' => 'required|date_format:Y-m',
            'late' => 'nullable|integer|min:0'
        ]);
    
        $month = $request->input('month');
        $late = $request->input('late', 0);
    
        Excel::import(new ScheduleImport($month, $late), $request->file('file'));
    
        return back()->with('success', 'Schedule imported successfully.');
    }
    
}

