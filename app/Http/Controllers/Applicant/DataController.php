<?php

namespace App\Http\Controllers\Applicant;

use App\Models\Site;
use App\Models\User;
use App\Models\Floor;
use App\Models\Career;
use App\Models\Document;
use App\Models\Applicant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DataController extends Controller
{
    public function registrationForm()
    {
        return view('website.registration-form');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $timelines = Applicant::where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->get();
        return view('website.dashboard', compact('timelines'));
    }

    public function faq()
    {
        return view('website.faq');
    }

    public function history()
    {
        $user = Auth::user();
        
        $applicants = Applicant::with(['career', 'status'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->unique('career_id'); 

        return view('website.history', compact('applicants'));
    }

    public function eletter()
    {
        $user = Auth::user();

        $letter = Letter::with(['career'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        return view('website.letter.show', compact('letter'));
    }

    public function index(Request $request)
    {
        $query = Career::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('date') && $request->date != '') {
            $query->whereDate('created_at', '>=', $request->date);
        }

        $careers = $query->addSelect([
            'applicants_count' => Applicant::selectRaw('count(distinct user_id)')
                ->whereColumn('career_id', 'careers.id')
        ])->get();

        return view('website.careers.index', compact('careers'));
    }

    public function detail($slug)
    {
        $career  = Career::where('slug', $slug)->firstOrFail();
        return view('website.careers.detail', compact('career'));
    }

    public function apply($slug)
    {
        $user = Auth::user();

        $requiredFields = [
            'marriage_status', 
            
        ];

        $isComplete = $user->profile && collect($requiredFields)->every(function($field) use ($user) {
            return !empty($user->profile->$field);
        });

        if (!$isComplete) {
            return redirect()->back()
                ->with('error', 'Profil tidak lengkap. Mohon dilengkapi Data diri anda.');
        }

        $career = Career::where('slug', $slug)->firstOrFail();
        
        if (Applicant::where(['user_id' => $user->id, 'career_id' => $career->id])->exists()) {
            return redirect()->back()->with('error', 'Anda sudah melamar posisi ini.');
        }

        Applicant::create([
            'user_id' => $user->id,
            'career_id' => $career->id,
            'status_id' => 0,
        ]);

        return redirect()->route('web.applicants.dashboard')
            ->with('success', 'Terimakasih telah melamar pekerjaan!');
    }

    public function indexProfile()
    {
        $sites = Site::all();
        $user = Auth::user();

        $documents = Document::where('user_id', $user->id)->get();
        return view('website.profiles.index', compact('user', 'sites', 'documents'));
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
