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
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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

        return view('profiles.index', compact('user', 'users', 'userSites', 'sites', 'roles', 'userRoles', 'mutations', 'documents', 'activities', 'notificationSettings', 'userHasSignLeader'));
    }

    public function updateAccount(Request $request)
    {
        $user = Auth::user();
        $input = $request->all();
    
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
            'address', 'gender', 'birth_place', 'birth_date', 'mother_name', 
            'npwp_number', 'marriage_status', 'bank_name', 'account_name', 
            'account_number', 'resign_date'
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->profile && $user->profile->avatar_public_id) {
                Cloudinary::destroy($user->profile->avatar_public_id); 
            }

            $cloudinaryImage = $request->file('avatar')->storeOnCloudinary('avatars');
            
            $profileData['avatar_url'] = $cloudinaryImage->getSecurePath();
            $profileData['avatar_public_id'] = $cloudinaryImage->getPublicId();
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

}
