<?php

namespace App\Http\Controllers\Api\Supervisor;

use App\Models\Site;
use App\Models\TaskPlanner;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\SecurityPatroll;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReportPatroll extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // ambil semua site yang dipimpin user
        $userSites = $user->sites_leader;
        $userSiteIds = $userSites->pluck('id');

        if ($userSiteIds->isEmpty()) {
            return response()->json([
                'message' => 'No sites found',
                'data' => []
            ], 404);
        }

        // default site = site pertama user
        $defaultSiteId = $userSiteIds->first();

        // site yang dipilih user (jika tidak ada pakai default)
        $searchSite = $request->query('site', $defaultSiteId);

        // default turn = 1
        $turn = $request->query('turn_patroll', 1);

        // ambil site yang sesuai user + site terpilih
        $sitesQuery = Site::with(['patrolls.security_patroll', 'floors', 'taskPlanners'])
            ->whereIn('id', $userSiteIds);

        if ($searchSite) {
            $sitesQuery->where('id', $searchSite);
        }

        $sites = $sitesQuery->get();

        $results = [];

        foreach ($sites as $site) {
            // ambil task planner berdasarkan tanggal sekarang
            $taskPlanners = $site->taskPlanners()
                ->where('date', Carbon::today()->toDateString())
                // ->where('date', '2025-09-28') // untuk develop
                ->whereIn('floor_id', $site->floors->pluck('id'))
                ->get();

            // cek status tiap floor
            $floorsStatus = [];
            foreach ($site->floors as $floor) {
                // ambil patrolls sesuai turn patroli
                $patrolls = $site->patrolls
                    ->where('turn', $turn)
                    ->where('date', Carbon::today()->toDateString());

                if (!$patrolls->isEmpty()) {
                    // cek apakah floor ini ada di task planner
                    $taskExists = $taskPlanners->where('floor_id', $floor->id)->count();

                    // cek apakah sudah patroli
                    if ($patrolls->isNotEmpty()) {
                        $reported = $patrolls->pluck('security_patroll')->first()
                            ->where('floor_id', $floor->id)->count();
                    } else {
                        $reported = 0;
                    }
                    $floorsStatus[] = [
                        'floor_id'    => $floor->id,
                        'floor_name'  => $floor->name,
                        'task_planned' => $taskExists,
                        'reported' => $reported,
                        'status'      => $reported ? 'Terlapor' : 'Belum Patroli',
                    ];
                } else {
                    $floorsStatus = [];
                }
            }

            $results[] = [
                'site_id'    => $site->id,
                'site_name'  => $site->name,
                'turn'       => $turn,
                'floors'     => $floorsStatus,
            ];
        }

        // ambil semua site user
        $sites = Site::with('patrolls')
            ->whereIn('id', $userSiteIds)
            ->get();

        // tentukan site yang dipilih user atau default site pertama
        $selectedSiteId = $request->query('site', $userSiteIds->first());

        // cari site yang dipilih user
        $selectedSiteModel = $sites->where('id', $selectedSiteId)->first();

        // kalau site yang dipilih user tidak ada patrolls → ambil site dengan patroll terbanyak
        if (!$selectedSiteModel || $selectedSiteModel->patrolls->isEmpty()) {
            $selectedSiteModel = $sites->sortByDesc(function ($site) {
                return $site->patrolls->count();
            })->first();
        }

        // ambil daftar turn unik dari patrolls site terpilih
        $turns_list = $selectedSiteModel
            ? $selectedSiteModel->patrolls
            ->where('date', Carbon::today()->toDateString())
            ->pluck('turn')
            ->unique()
            ->values()
            : collect([]);

        // ID site terpilih
        $selectedSiteId = $searchSite;

        // ambil nama site terpilih
        $selectedSiteName = optional(
            $userSites->where('id', $selectedSiteId)->first()
        )->name;

        return response()->json([
            'selected_site' => $selectedSiteName,    // untuk frontend
            'selected_turn' => $turn,          // untuk frontend
            'sites_list'    => $userSites->pluck('name', 'id'), // dropdown pilih site
            'turns_list'    => $turns_list, // dropdown pilih turn
            'sites'         => $results
        ]);
    }
}
