<?php

namespace App\Http\Controllers\Api\Supervisor;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ManageTimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil parameter search
        $searchSite = $request->query('site');  // ?site=Tim Pengembang
        $searchUser = $request->query('user');  // ?user=Budi

        // Query sites_leader + eager load users + profile
        $userSitesQuery = $user->sites_leader()->with([
            'users' => function ($q) use ($searchUser) {
                if ($searchUser) {
                    $q->where('name', 'like', "%{$searchUser}%");
                }
                // load relasi profil user
                $q->with(['profile', 'site:id,name', 'roles', 'attendances' => function($att){
                    $att->where('date', Carbon::today()->toDateString());
                }]);

            }
        ]);

        // Filter site jika ada parameter site
        if ($searchSite) {
            $userSitesQuery->where('name', 'like', "%{$searchSite}%");
        }

        $userSites = $userSitesQuery->get();

        if ($userSites->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        // Map hasil siteName => [users+profile]
        $users = $userSites->mapWithKeys(function ($site) {
            // reset index agar JSON rapih
            return [$site->name => $site->users->values()];
        });

        // Ambil list nama site saja
        $sites = $userSites->pluck('name');

        return response()->json([
            'message' => 'Data ditemukan',
            'sites' => $sites,
            'data' => $users
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
