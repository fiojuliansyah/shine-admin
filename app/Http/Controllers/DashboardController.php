<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Career;
use App\Models\Company;
use App\Models\Document;
use App\Models\Site;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        $careerCount = Career::count();
        $siteCount = Site::count();
        $companyCount = Company::count();
        $userCount = User::where('is_employee', 1)->count();
        $applicantCount = User::whereNull('is_employee')
                        ->whereHas('applicants', function($q) {
                            $q->whereNull('done');
                        })->count();

        $roles = Role::withCount('users')->get();
        $rolesData = $roles->map(function($role) {
            return [
                'name' => $role->name,
                'count' => $role->users_count
            ];
        });

        $statuses = Status::all();
        $statusData = [];
        $totalApplicants = 0;

        foreach ($statuses as $status) {
            $count = Applicant::where('status_id', $status->id)
                                ->where('done', null)
                                ->count();
            $statusData[$status->name] = [
                'id' => $status->id,
                'name' => $status->name,
                'count' => $count
            ];
            $totalApplicants += $count;
        }

        foreach ($statusData as $name => $data) {
            $percentage = $totalApplicants > 0 ? round(($data['count'] / $totalApplicants) * 100) : 0;
            $statusData[$name]['percentage'] = $percentage;
        }

        $topPosition = Career::select('careers.id', 'careers.name', 'careers.candidate')
            ->leftJoin('applicants', 'applicants.career_id', '=', 'careers.id')
            ->selectRaw('count(distinct applicants.user_id) as applicants_count')
            ->groupBy('careers.id', 'careers.name', 'careers.candidate')
            ->orderBy('applicants_count', 'desc')
            ->first();

        $latestJobs = Career::orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        $latestApplicants = Applicant::with(['user', 'career'])
                            ->orderBy('created_at', 'desc')
                            ->take(4)
                            ->get();

        $recentEmployees = User::where('is_employee', 1)
                      ->with('roles')
                      ->orderBy('created_at', 'desc')
                      ->take(5)
                      ->get();

        return view('admin.dashboards.dashboard-gic', compact(
            'siteCount',
            'careerCount',
            'userCount',
            'applicantCount',
            'companyCount',
            'rolesData',
            'statusData',
            'totalApplicants',
            'topPosition',
            'latestJobs',
            'latestApplicants',
            'recentEmployees'
        ));
    }

    public function recruit()
    {
        $career = Career::count();
        $applicant = Applicant::whereNull('done')
                        ->where('status_id', 0)
                        ->count();

        $statuses = Status::all();

        $applicantCounts = [];
        foreach ($statuses as $status) {
            $applicantCounts[$status->id] = Applicant::where('status_id', $status->id)
                                        ->whereNotNull('approve_id')
                                        ->whereNull('done')
                                        ->count();
        }

        return view('admin.dashboards.recruit', compact('statuses', 'applicant', 'applicantCounts', 'career'));
    }

    public function comingsoon()
    {
        return view('admin.dashboards.soon');
    }

    public function activities()
    {
        return view('admin.dashboards.activities');
    }

    public function welcome()
    {
        return view('admin.landing');
    }

    public function career()
    {
        return view('website.careers.index');
    }

    public function careerDetail($id)
    {
        $user = Auth::user();
        $documents = $user ? Document::where('user_id', $user->id)->get() : collect();

        $ID = decrypt($id);
        $career = Career::find($ID);
        return view('website.careers.detail', compact('career', 'user', 'documents'));
    }

    public function indexAccount()
    {
        $sites = Site::all();
        $user = Auth::user();
        return view('website.profiles.index', compact('user', 'sites'));
    }

    public function indexProfile()
    {
        $user = Auth::user();
        return view('website.profiles.profile', compact('user'));
    }

    public function indexDocument()
    {
        $user = Auth::user();
        $documents = Document::where('user_id', $user->id)->get();
        return view('website.profiles.document', compact('user', 'documents'));
    }

    public function whatsappConfig()
    {
        return view('admin.dashboards.whatsapp-config');
    }

    public function getWhatsappStatus()
    {
        $token = config('services.fonnte.token');

        if (!$token) {
            return response()->json([
                'status' => false,
                'reason' => 'Token Fonnte (FONNTE_TOKEN) belum diatur di .env server.',
            ]);
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->timeout(20)
                ->post('https://api.fonnte.com/qr', ['type' => 'qr']);
        } catch (\Throwable $e) {
            Log::warning('Fonnte QR: gagal menghubungi server', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'reason' => 'Server Fonnte tidak merespons (timeout). Coba lagi beberapa saat.',
            ]);
        }

        $data = $response->json();

        if (!is_array($data)) {
            Log::warning('Fonnte QR: respons bukan JSON', ['http' => $response->status(), 'body' => mb_substr($response->body(), 0, 500)]);

            return response()->json([
                'status' => false,
                'reason' => 'Respons Fonnte tidak valid (HTTP ' . $response->status() . '). Coba lagi beberapa saat.',
            ]);
        }

        $connected = ($data['reason'] ?? null) === 'device already connect' || ($data['status'] ?? null) === 'connected';
        if (!$connected && empty($data['url'])) {
            Log::info('Fonnte QR: QR tidak tersedia', ['response' => $data]);
        }

        // Status koneksi dipakai halaman lupa password pelamar; segarkan cache-nya.
        \Illuminate\Support\Facades\Cache::put('fonnte_connected', $connected, 60);

        return response()->json($data + ['connected' => $connected]);
    }

    public function disconnectWhatsapp()
    {
        try {
            $token = config('services.fonnte.token');

            $response = Http::withHeaders([
                'Authorization' => $token
            ])->timeout(15)->post('https://api.fonnte.com/disconnect');

            return response()->json($response->json());

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'reason' => 'Gagal memutuskan koneksi'
            ], 500);
        }
    }
}
