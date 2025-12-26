<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use App\Models\Permit;
use Illuminate\Http\Request;
use App\Exports\PermitExport;
use App\DataTables\PermitsDataTable;
use Maatwebsite\Excel\Facades\Excel;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class PermitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PermitsDataTable $dataTable, Request $request)
    {
        $users = User::all();
        $sites = Site::all();
        
        // Get filter values from request
        $filters = [
            'date' => $request->date,
            'user_id' => $request->user_id,
            'site_id' => $request->site_id,
            'status' => $request->status,
        ];
        
        // Counts for dashboard cards
        $pendingCount = Permit::where('status', 'pending')->count();
        $approvedCount = Permit::where('status', 'approved')->count();
        $rejectedCount = Permit::where('status', 'rejected')->count();
        $permitCount = Permit::count();
        
        return $dataTable->render('attendances.permits.index', compact(
            'users', 
            'sites',
            'pendingCount',
            'permitCount',
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
        $permit = new Permit;
        $permit->user_id = $request->user_id;
        $permit->title = $request->title;
        $permit->site_id = $request->site_id;
        $permit->start_date = $request->start_date;
        $permit->end_date = $request->end_date;
        $permit->reason = $request->reason;
        $permit->contact = $request->contact;

        if ($request->hasFile('image')) {
            $cloudinaryImage = $request->file('image')->storeOnCloudinary('permits_images');
            $permit->image_url = $cloudinaryImage->getSecurePath();
            $permit->image_public_id = $cloudinaryImage->getPublicId();
        }

        $permit->save();

        return redirect()->route('permits.index')
                        ->with('success', 'Leave successfully created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permit $permit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permit $permit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $permit = Permit::findOrFail($id);

        $permit->fill($request->only([
            'user_id', 'title', 'site_id', 'start_date', 'end_date', 'reason', 'contact', 'status'
        ]));

        if ($request->hasFile('image')) {
            if ($permit->image_public_id) {
                Cloudinary::destroy($permit->image_public_id);
            }

            $cloudinaryImage = $request->file('image')->storeOnCloudinary('permits_images');
            $permit->image_url = $cloudinaryImage->getSecurePath();
            $permit->image_public_id = $cloudinaryImage->getPublicId();
        }

        $permit->save();

        return redirect()->route('permits.index')
                        ->with('success', 'Leave successfully updated.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $permit = Permit::findOrFail($id);

        if ($permit->image_public_id) {
            Cloudinary::destroy($permit->image_public_id);
        }

        $permit->delete();

        return redirect()->route('permits.index')
                        ->with('success', 'Leave successfully deleted.');
    }

    public function export(Request $request)
    {
        $site_id = $request->site_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $filename = 'ijin_(' . $start_date . ')-(' . $end_date . ').xlsx';

        return Excel::download(new PermitExport($site_id, $start_date, $end_date), $filename);
    }
}
