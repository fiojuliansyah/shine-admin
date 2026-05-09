<?php

namespace App\Http\Controllers;

use App\DataTables\ApplicantsDataTable;
use App\Models\Applicant;
use App\Models\Career;
use App\Models\Document;
use App\Models\Site;
use App\Models\Status;
use App\Notifications\ApplicantStatusNotification;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class ApplicantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ApplicantsDataTable $dataTable)
    {
        $careers = Career::all();
        $title = 'Manage Applicant';
        return $dataTable->render('admin.applicants.index', compact('careers','title'));
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
        $user = Auth::user()->id;

        $applicant = new Applicant;
        $applicant->user_id = $user;
        $applicant->career_id = $request->career_id;
        $applicant->status_id = '0';
        $applicant->save();

        return redirect()->route('web-career')
                        ->with('success', 'Career ' . $applicant->user['name'] . ' berhasil dibuat');
    }


    public function show(Applicant $applicant)
    {
        
    }

    public function updateStatus(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);

        $newStatus = $request->status_id;

        if ($newStatus) {
            $status = Status::findOrFail($newStatus);

            $applicantData = [
                'user_id' => $applicant->user_id,
                'career_id' => $applicant->career_id,
                'status_id' => $newStatus,
            ];

            if ($status->is_approve == 0) {
                $applicantData['approve_id'] = '0';
            }

            Applicant::create($applicantData);
        }

        $applicant->done = 'done';
        $applicant->save();

        return redirect()->back()->with('success', 'Applicant berhasil diperbarui');
    }


    public function updateApprove(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);

        $applicant->approve_id = $request->approve_id;
        $applicant->update();

        return redirect()->back()
                        ->with('success', 'Applicant berhasil diperbarui');
    }

    public function destroy($id)
    {
        try {
            $applicant = Applicant::findOrFail($id);
            
            $applicant->delete();

            return redirect()->back()->with('success', 'Data pelamar berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function resume($id)
    {
        $applicant = Applicant::with(['user.profile', 'career', 'user.document'])->findOrFail($id);
        $user = $applicant->user;
        $documents = $user->document;
        $roles = Role::all();
        $statuses = Status::all();
        $sites = Site::all();

        return view('admin.applicants.resume', compact('applicant', 'user', 'documents', 'statuses', 'roles', 'sites'));
    }

    public function updateStatusSingle(Request $request, $id)
    {
        $request->validate([
            'status_id' => 'required|exists:statuses,id',
            'site_id'   => 'nullable|exists:sites,id', // Tambahkan validasi site
            'role_name' => 'nullable|string'
        ]);

        $applicant = Applicant::findOrFail($id);
        
        // 1. Update Status Applicant
        $applicant->status_id = $request->status_id;
        $applicant->save();

        // 2. Update Data User (Role & Site)
        if ($applicant->user) {
            // Update Site ID
            if ($request->has('site_id')) {
                $applicant->user->update([
                    'site_id' => $request->site_id
                ]);
            }

            // Update Role (Spatie)
            if ($request->role_name) {
                $applicant->user->syncRoles([$request->role_name]);
            }
        }

        try {
            $applicant->load(['status', 'career']);
            $applicant->notify(new ApplicantStatusNotification($applicant));
        } catch (\Exception $e) {
            return redirect()->back()->with('success', 'Data diperbarui (termasuk Site), tapi notifikasi gagal.');
        }

        return redirect()->back()->with('success', 'Status, Site, dan Role berhasil diperbarui.');
    }

}
