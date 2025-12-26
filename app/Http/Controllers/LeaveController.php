<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Site;
use App\Models\User;
use App\Models\Leave;
use App\Models\TypeLeave;
use App\Exports\LeaveExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\DataTables\LeavesDataTable;
use Maatwebsite\Excel\Facades\Excel;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LeavesDataTable $dataTable, Request $request)
    {
        $types = TypeLeave::all();
        $users = User::all();
        $sites = Site::all();
        
        // Get filter values from request
        $filters = [
            'date' => $request->date,
            'type_id' => $request->type_id,
            'user_id' => $request->user_id,
            'site_id' => $request->site_id,
            'status' => $request->status,
        ];
        
        // Counts for dashboard cards
        $pendingCount = Leave::where('status', 'pending')->count();
        $approvedCount = Leave::where('status', 'approved')->count();
        $rejectedCount = Leave::where('status', 'rejected')->count();
        $leaveCount = Leave::count();
        
        return $dataTable->render('attendances.leaves.index', compact(
            'types', 
            'users', 
            'sites',
            'pendingCount',
            'leaveCount',
            'approvedCount',
            'rejectedCount',
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
        $request->validate([
            'user_id' => 'required|string',
            'type_id' => 'required|string',
            'site_id' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reason' => 'nullable|string',
            'contact' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $leave = new Leave;
        $leave->user_id = $request->user_id;
        $leave->type_id = $request->type_id;
        $leave->site_id = $request->site_id;
        $leave->start_date = $request->start_date;
        $leave->end_date = $request->end_date;
        $leave->reason = $request->reason;
        $leave->contact = $request->contact;

        if ($request->hasFile('image')) {
            $cloudinaryImage = $request->file('image')->storeOnCloudinary('leaves_images');
            $leave->image_url = $cloudinaryImage->getSecurePath();
            $leave->image_public_id = $cloudinaryImage->getPublicId();
        }

        $leave->save();

        return redirect()->route('leaves.index')
                        ->with('success', 'Leave successfully created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Leave $leave)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Leave $leave)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|string',
            'type_id' => 'required|string',
            'site_id' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reason' => 'nullable|string',
            'contact' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $leave = Leave::findOrFail($id);
        $leave->user_id = $request->user_id;
        $leave->type_id = $request->type_id;
        $leave->site_id = $request->site_id;
        $leave->start_date = $request->start_date;
        $leave->end_date = $request->end_date;
        $leave->reason = $request->reason;
        $leave->contact = $request->contact;

        if ($request->hasFile('image')) {
            Cloudinary::destroy($leave->image_public_id);
            $cloudinaryImage = $request->file('image')->storeOnCloudinary('leaves_images');
            $leave->image_url = $cloudinaryImage->getSecurePath();
            $leave->image_public_id = $cloudinaryImage->getPublicId();
        }

        $leave->save();

        return redirect()->route('leaves.index')
                        ->with('success', 'Leave successfully updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->image_public_id) {
            Cloudinary::destroy($leave->image_public_id);
        }

        $leave->delete();

        return redirect()->route('leaves.index')
                        ->with('success', 'Leave successfully deleted.');
    }

    public function cleanDuplicateLeaves()
    {
        $duplicates = DB::table('leaves')
            ->select('type_id', 'user_id', 'site_id', 'start_date', 'end_date', 'reason', 'contact', DB::raw('COUNT(*) as count'))
            ->groupBy('type_id', 'user_id', 'site_id', 'start_date', 'end_date', 'reason', 'contact')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Ambil data leaves yang duplikat berdasarkan kombinasi yang sama
            $leavesToDelete = Leave::where('type_id', $duplicate->type_id)
                ->where('user_id', $duplicate->user_id)
                ->where('site_id', $duplicate->site_id)
                ->where('start_date', $duplicate->start_date)
                ->where('end_date', $duplicate->end_date)
                ->where('reason', $duplicate->reason)
                ->where('contact', $duplicate->contact)
                ->get();

            $keepLeave = $leavesToDelete->sortByDesc('id')->first();  // ID terbesar dipilih untuk dipertahankan

            // Hapus data duplikat lainnya
            foreach ($leavesToDelete as $leave) {
                if ($leave->id !== $keepLeave->id) {
                    $leave->delete();
                }
            }
        }

        return redirect()->route('leaves.index')->with('success', 'Duplicate leaves cleaned successfully.');
    }

    public function export(Request $request)
    {
        $site_id = $request->site_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $filename = 'cuti_(' . $start_date . ')-(' . $end_date . ').xlsx';

        return Excel::download(new LeaveExport($site_id, $start_date, $end_date), $filename);
    }

}
