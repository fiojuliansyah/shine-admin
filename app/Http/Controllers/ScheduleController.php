<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
        return $dataTable->render('admin.schedules.index');
    }

    public function show($siteId)
    {
        $site = Site::findOrFail($siteId);

        $sites = site::all();
        
        $schedules = Schedule::where('site_id', $siteId)->with('user')->get();
        $shifts = Shift::where('site_id', $siteId)->get();
    
        $dates = $schedules->pluck('date')->unique()->sort()->values();
    
        $groupedSchedules = $schedules->groupBy('user_id');
    
        return view('admin.schedules.show', compact('site', 'dates', 'groupedSchedules', 'sites', 'shifts'));
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

    public function clean(Request $request, $siteId)
    {
        $site = Site::findOrFail($siteId);
        $query = Schedule::where('site_id', $siteId);

        if ($request->filled('filter_month')) {
            $month = Carbon::parse($request->filter_month)->month;
            $year = Carbon::parse($request->filter_month)->year;

            $query->whereMonth('date', $month)
                ->whereYear('date', $year);
            
            $message = "Jadwal project {$site->name} bulan {$request->filter_month} berhasil dibersihkan.";
        } else {
            $message = "Seluruh jadwal project {$site->name} berhasil dibersihkan.";
        }

        $query->delete();

        return redirect()->back()->with('success', $message);
    }

    public function shiftUpdate(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'shift_code' => 'required|string|max:10',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'type' => 'nullable|in:off,leave',
        ]);

        $clockIn = $request->clock_in;
        $clockOut = $request->clock_out;

        $shift->update([
            'name' => $request->name,
            'shift_code' => $request->shift_code,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'type' => $request->type ?? null,
        ]);

        return redirect()->back()->with('success', 'Shift berhasil diubah.');
    }

    public function shiftDestroy($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();

        return redirect()->back()->with('success', 'Shift berhasi dihapus.');
    }
}

