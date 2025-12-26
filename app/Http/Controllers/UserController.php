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
use App\DataTables\UsersDataTable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class UserController extends Controller
{
    public function index(UsersDataTable $dataTable)
    {
        $sites = Site::all();
        return $dataTable->render('users.index', compact('sites'));
    }

    public function indexResume($id)
    {
        $user = User::findOrFail($id);
        return view('users.profiles.resume', compact('user'));
    }

    public function indexAccount($id)
    {
        $user = User::with('notificationSettings')->findOrFail($id);

        $notificationSettings = $user->notificationSettings;

        $users = User::where('has_sign_leader', 1)->get();
        $sites = Site::all();
        $roles = Role::pluck('name', 'name')->all();
        $userRoles = $user->roles->pluck('name', 'name')->all();
        $mutations = Mutation::where('user_id', $id)->get();
        $activities = Activity::where('causer_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $documents = Document::where('user_id', $user->id)->get();
        $sites = Site::all();
        $userSites = User::with('sites_leader')->find($id);
        $userHasSignLeader = User::where('id', $id)->first();

        return view('users.profiles.index', compact('user', 'users', 'userSites', 'sites', 'roles', 'userRoles', 'mutations', 'documents', 'activities', 'notificationSettings', 'userHasSignLeader'));
    }


    public function updateAccount(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $input = $request->all();

        // Update site_id tunggal (kalau memang user punya kolom site_id sendiri)
        if ($request->has('site_id')) {
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
        $profileData = $request->only([
            'address',
            'gender',
            'birth_place',
            'birth_date',
            'mother_name',
            'npwp_number',
            'marriage_status',
            'bank_name',
            'account_name',
            'account_number',
            'resign_date'
        ]);

        if ($request->hasFile('avatar')) {
            $cloudinaryImage = $request->file('avatar')->storeOnCloudinary('avatars');
            $profileData['avatar_url'] = $cloudinaryImage->getSecurePath();
            $profileData['avatar_public_id'] = $cloudinaryImage->getPublicId();
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

        $cloudinaryFile = $request->file('file')->storeOnCloudinary('Documents');
        $url = $cloudinaryFile->getSecurePath();
        $public_id = $cloudinaryFile->getPublicId();

        $document = new Document;
        $document->user_id = $user->id;
        $document->name = $request->name;
        $document->description = $request->description ?? null;
        $document->validate = $request->validate ?? null;
        $document->file_url = $url;
        $document->file_public_id = $public_id;
        $document->save();

        return redirect()->back()->with('success', 'Dokumen ' . $document->name . ' berhasil diunggah');
    }

    public function storeMutation(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $mutation = new Mutation;
        $mutation->date = $request->date;
        $mutation->user_id = $user->id;
        $mutation->from_id = $user->site_id;
        $mutation->to_id = $request->to;
        $mutation->description = $request->description;
        $mutation->save();

        $user->site_id = $request->to;
        $user->save();

        return redirect()->back()->with('success', 'Mutasi ' . $user->name . ' berhasil dilakukan');
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
        User::find($id)->delete();
        return redirect()->back()
            ->with('success', 'Berhasil Dihapus');
    }

    public function import(Request $request)
    {
         $request->validate([
            'site_id' => 'required|exists:sites,id',
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new EmployeeImport($request->site_id), $request->file('file')->store('files'));
        return redirect()->back()->with('success', 'Data pegawai berhasil diimport!');
    }

    public function export(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id'
        ]);
        $filename = 'users_' . date('d-M-Y') . '.xlsx';
        return Excel::download(new UserExport($request->site_id), $filename);
    }
}
