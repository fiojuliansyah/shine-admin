<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use App\Models\Profile;
use App\Models\Document;
use App\Models\Mutation;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Spatie\Activitylog\Models\Activity;
use App\Services\FileStorage;
use App\Support\IdentityRules;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load('notificationSettings');
        $notificationSettings = $user->notificationSettings;

        $users = User::where('has_sign_leader', 1)->get();
        $sites = Site::all();
        $roles = Role::pluck('name', 'name')->all();
        $userRoles = $user->roles->pluck('name', 'name')->all();
        $mutations = Mutation::where('user_id', $user->id)->get();
        $activities = Activity::where('causer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $documents = Document::where('user_id', $user->id)->get();
        $sites = Site::all();
        $userSites = User::with('sites_leader')->find($user->id);
        $userHasSignLeader = User::where('id', $user->id)->first();

        return view('admin.profiles.index', compact('user', 'users', 'userSites', 'sites', 'roles', 'userRoles', 'mutations', 'documents', 'activities', 'notificationSettings', 'userHasSignLeader'));
    }

    public function updateAccount(Request $request)
    {
        $user = Auth::user();

        IdentityRules::normalize($request, ['nik']);
        $request->validate([
            'nik' => IdentityRules::nik(required: (bool) $user->is_employee),
            'email' => ['nullable', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
        ], IdentityRules::messages());

        // Hanya data akun milik sendiri; kolom HR (is_employee, employee_nik, site_id, dst.) tidak boleh diubah sendiri.
        $input = $request->only(['name', 'email', 'nik', 'phone', 'password']);
    
        if (isset($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            unset($input['password']);
        }
    
        $user->update($input);
    
        return redirect()->back()
                        ->with('success', 'Profil ' . $user->name . ' berhasil diperbarui');
    }  

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $profileData = $request->only([
            'address', 'rt_rw', 'kelurahan', 'kecamatan', 'gender', 'birth_place', 'birth_date', 'mother_name', 
            'npwp_number', 'marriage_status', 'bank_name', 'account_name', 
            'account_number', 'join_date', 'resign_date',
            'emergency_name', 'emergency_phone', 'emergency_relation', 'emergency_address'
        ]);

        if ($request->hasFile('avatar')) {
            $request->validate(['avatar' => 'image|mimes:jpg,jpeg,png,webp|max:5120'], ['avatar.image' => 'Pas foto harus berupa gambar.', 'avatar.mimes' => 'Pas foto harus JPG/PNG/WebP.', 'avatar.max' => 'Ukuran pas foto maksimal 5 MB.']);
            $avatar = FileStorage::upload($request->file('avatar'), 'avatars');
            FileStorage::delete($user->profile?->avatar_public_id);

            $profileData['avatar_url'] = $avatar['url'];
            $profileData['avatar_public_id'] = $avatar['path'];
        }
        
        $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return redirect()->back()->with('success', 'Profil ' . $user->name . ' berhasil diperbarui');
    }


    public function storeDocument(Request $request)
    {
        $user = Auth::user();
    
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

}
