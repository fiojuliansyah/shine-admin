<?php

namespace App\Http\Controllers;

use App\DataTables\ApplicantsDataTable;
use App\Models\Applicant;
use App\Models\Career;
use App\Models\Document;
use App\Models\EmployeeNikConfig;
use App\Models\Site;
use App\Models\Status;
use App\Models\User;
use App\Notifications\ApplicantStatusNotification;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
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

            // 3. Generate NIK Karyawan & set is_employee = 1
            //    Pakai konfigurasi NIK milik company dari site terpilih.
            $siteId = $request->site_id ?? $applicant->user->site_id;
            if ($siteId) {
                $site = Site::with('company')->find($siteId);
                if ($site && $site->company) {
                    $nikConfig = EmployeeNikConfig::defaultForCompany($site->company_id);
                    if (!$nikConfig) {
                        return redirect()->back()->with('error',
                            'Status diperbarui, tetapi Company "' . $site->company->name . '" belum memiliki konfigurasi NIK Karyawan. ' .
                            'Silakan atur di menu Konfigurasi NIK terlebih dahulu.');
                    }

                    $startDate = $applicant->user->profile?->join_date
                        ?? optional($applicant->created_at)->toDateString()
                        ?? now()->toDateString();

                    // Refresh roles cache supaya kode_jabatan ikut role baru.
                    $applicant->user->load('roles');

                    // Idempoten: jangan bakar nomor urut kalau user sudah punya NIK.
                    if (!empty($applicant->user->employee_nik)) {
                        $applicant->user->update([
                            'is_employee' => 1,
                        ]);
                    } else {
                        DB::transaction(function () use ($nikConfig, $applicant, $startDate) {
                            $employeeNIK = $nikConfig->generateNik($applicant->user, $startDate);

                            $applicant->user->update([
                                'employee_nik' => $employeeNIK,
                                'is_employee'  => 1,
                            ]);
                        });
                    }
                }
            }
        }

        try {
            $applicant->load(['status', 'career']);
            $applicant->notify(new ApplicantStatusNotification($applicant));
        } catch (\Exception $e) {
            return redirect()->back()->with('success', 'Data diperbarui (termasuk Site & NIK), tapi notifikasi gagal.');
        }

        return redirect()->back()->with('success', 'Status, Site, Role, dan NIK Karyawan berhasil diperbarui.');
    }

    public function setEmployee(Request $request, $id)
    {
        $applicant = Applicant::findOrFail($id);

        if (!$applicant->user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        $applicant->user->update([
            'is_employee' => 1,
        ]);

        return redirect()->back()->with('success', $applicant->user->name . ' berhasil dijadikan karyawan.');
    }

    public function resetAllQr()
    {
        try {
            $users = User::all();

            foreach ($users as $user) {
                $link = route('applicants.resume', ['id' => $user->id]);
                $qrCodeSvg = QrCode::format('svg')->size(300)->generate($link);

                $user->update([
                    'profile_qr' => $qrCodeSvg,
                ]);
            }

            return response()->json(['message' => 'Berhasil mereset ' . $users->count() . ' QR Code.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

}
