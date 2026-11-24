<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Site;
use App\Models\User;
use App\Models\Document;
use App\Models\Mutation;
use App\Exports\UserExport;
use App\Models\UserHasSites;
use Illuminate\Http\Request;
use App\Imports\EmployeeImport;
use App\Models\UserNotification;
use App\Services\FileStorage;
use App\Support\IdentityRules;
use App\DataTables\AdminsDataTable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class AdminController extends Controller
{
    public function index(AdminsDataTable $dataTable)
    {
        $sites = Site::orderBy('name', 'asc')->get();

        return $dataTable->render('admin.admins.index', compact('sites'));
    }

    public function indexResume($id)
    {
        $user = User::findOrFail($id);
        return view('admin.admins.profiles.resume', compact('user'));
    }

    public function indexAccount($id)
    {
        $user = User::with('notificationSettings')->findOrFail($id);

        $notificationSettings = $user->notificationSettings;

        $users = User::where('has_sign_leader', 1)->get();
        $sites = Site::all();
        $roles = Role::pluck('name', 'name')->all();
        $userRoles = $user->roles->pluck('name', 'name')->all();
        $mutations = Mutation::with(['from', 'to', 'creator', 'generate.letter.numberConfig', 'generate.site.company'])->where('user_id', $id)->orderByDesc('date')->orderByDesc('id')->get();
        // Template surat untuk proses karier (mutasi/promosi/demosi/resign)
        $movementLetters = \App\Models\Letter::with(['site:id,name', 'type:id,name'])
            ->orderBy('title')
            ->get(['id', 'title', 'site_id', 'type_letter_id']);
        $roleOptions = EmployeeMovementController::roleOptions();
        $contracts = $user->generates()->with('letter.type')->whereNotNull('start_date')->latest()->get();
        $activities = Activity::where('causer_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $documents = Document::where('user_id', $user->id)->get();
        $sites = Site::all();
        $userSites = User::with('sites_leader')->find($id);
        $userHasSignLeader = User::where('id', $id)->first();

        return view('admin.admins.profiles.index', compact('user', 'users', 'userSites', 'sites', 'roles', 'userRoles', 'mutations', 'documents', 'activities', 'notificationSettings', 'userHasSignLeader', 'roleOptions', 'contracts', 'movementLetters'));
    }


    public function updateAccount(Request $request, $id)
    {
        $user = User::findOrFail($id);

        IdentityRules::normalize($request, ['nik']);
        $request->validate([
            'nik' => IdentityRules::nik(required: (bool) $user->is_employee),
            'email' => ['nullable', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
        ], IdentityRules::messages());

        // Hanya kolom yang memang ada di form Edit Akun (bukan $request->all(), yang bisa
        // dipakai mengubah kolom lain seperti is_employee lewat request yang dimodifikasi).
        $input = $request->only(['name', 'email', 'nik', 'phone', 'employee_nik', 'leader_id', 'department_id', 'password']);

        // Update site_id tunggal; perubahan site dicatat sebagai riwayat mutasi
        if ($request->has('site_id')) {
            if (filled($request->site_id) && (string) $request->site_id !== (string) $user->site_id) {
                Mutation::create([
                    'type' => 'mutasi',
                    'date' => now()->toDateString(),
                    'user_id' => $user->id,
                    'from_id' => $user->site_id,
                    'to_id' => $request->site_id,
                    'description' => 'Perubahan Site Utama melalui Edit Akun',
                    'created_by' => Auth::id(),
                ]);
            }

            $user->site_id = $request->site_id;
            $user->save();
        }

        if($request->has('has_sign_leader')){
            $user->has_sign_leader = 1;
            $user->save();
        } else {
            $user->has_sign_leader = 0;
            $user->save();
        }
        // Update user data
        if (isset($input['password']) && $input['password'] != '') {
            $input['password'] = Hash::make($input['password']);
        } else {
            unset($input['password']);
        }
        $user->update($input);

        // Update roles
        DB::table('model_has_roles')->where('model_id', $id)->delete();
        $user->assignRole($request->input('roles'));

        // Update notification settings
        $notificationSettings = $user->notificationSettings()->firstOrNew(['user_id' => $user->id]);
        $notificationSettings->user_id = $user->id;
        $notificationSettings->job_portal_email = $request->has('job_portal_email');
        $notificationSettings->job_portal_sms = $request->has('job_portal_sms');
        $notificationSettings->job_portal_whatsapp = $request->has('job_portal_whatsapp');
        $notificationSettings->job_portal_push = $request->has('job_portal_push');
        $notificationSettings->save();

        // **Update multi-sites**
        // otomatis hapus/insert sesuai array terbaru (klik X di select2 = terhapus di pivot)
        $user->sites_leader()->sync($request->sites ?? []);

        return redirect()->back()->with('success', 'Profil ' . $user->name . ' berhasil diperbarui');
    }



    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        IdentityRules::normalize($request, ['no_kk', 'account_number']);
        $request->validate(['no_kk' => IdentityRules::kk()] + IdentityRules::bank(), IdentityRules::messages());

        $profileData = $request->only([
            'no_kk',
            'address',
            'rt_rw',
            'kelurahan',
            'kecamatan',
            'gender',
            'birth_place',
            'birth_date',
            'mother_name',
            'npwp_number',
            'marriage_status',
            'bank_name',
            'account_name',
            'account_number',
            'join_date',
            'resign_date',
            'emergency_name',
            'emergency_phone',
            'emergency_relation',
            'emergency_address',
        ]);

        // Status karyawan selalu mengikuti tanggal resign
        if ($request->exists('resign_date')) {
            $profileData['employment_status'] = filled($profileData['resign_date'] ?? null) ? 'RESIGN' : 'ACTIVE';
        }

        if ($request->hasFile('avatar')) {
            $request->validate(['avatar' => 'image|mimes:jpg,jpeg,png,webp|max:5120'], ['avatar.image' => 'Pas foto harus berupa gambar.', 'avatar.mimes' => 'Pas foto harus JPG/PNG/WebP.', 'avatar.max' => 'Ukuran pas foto maksimal 5 MB.']);
            $avatar = FileStorage::upload($request->file('avatar'), 'avatars');
            $profileData['avatar_url'] = $avatar['url'];
            $profileData['avatar_public_id'] = $avatar['path'];
        }

        $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return redirect()->back()->with('success', $user->name . ' berhasil diperbarui');
    }

    public function storeDocument(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'file' => 'required|file|mimes:png,jpg,jpeg,pdf|max:2048',
            'name' => 'required|string|max:255',
        ]);

        $file = FileStorage::upload($request->file('file'), 'Documents');

        $document = new Document;
        $document->user_id = $user->id;
        $document->name = $request->name;
        $document->description = $request->description ?? null;
        $document->validate = $request->validate ?? null;
        $document->file_url = $file['url'];
        $document->file_public_id = $file['path'];
        $document->save();

        return redirect()->back()->with('success', 'Dokumen ' . $document->name . ' berhasil diunggah');
    }

    public function storeNotification(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $userNotif = new UserNotification;
        $userNotif->user_id = $user->id;
        $userNotif->module = $request->module;
        $userNotif->email = $request->email;
        $userNotif->sms = $request->sms;
        $userNotif->whatsapp = $request->whatsapp;
        $userNotif->push = $request->push;
        $userNotif->save();

        return redirect()->back()->with('success', 'Update Notifikasi ' . $user->name . ' berhasil dilakukan');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->notificationSettings()->delete(); 
        $user->delete();

        return redirect()->back()
            ->with('success', 'Berhasil Dihapus');
    }

    public function import(Request $request)
    {
         $request->validate([
            'site_id' => 'required|exists:sites,id',
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new EmployeeImport($request->site_id), $request->file('file'));
        return redirect()->back()->with('success', 'Data pegawai berhasil diimport!');
    }

    public function export(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id'
        ]);
        $filename = 'users_' . date('d-m-Y') . '.xlsx';
        return Excel::download(new UserExport($request->site_id), $filename);
    }
}
