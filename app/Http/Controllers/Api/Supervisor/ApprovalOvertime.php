<?php

namespace App\Http\Controllers\Api\Supervisor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Overtime;
use Illuminate\Support\Facades\Auth;

class ApprovalOvertime extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil parameter search
        $searchSite  = $request->query('site');
        $search = $request->query('search');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // ambil semua site_id yang dipimpin user login
        $sites   = $user->sites_leader;
        $siteIds = $sites->pluck('id');

        // jika supervisor tidak punya site
        if ($siteIds->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada site',
                'data'    => []
            ], 404);
        }

        // query overtime
        $overtimesQuery = Overtime::with([
            'site:id,name',
            'attendance.user:id,name',
        ])
            ->whereIn('site_id', $siteIds);

        // filter berdasarkan nama site (dari tabel site)
        if ($searchSite) {
            $overtimesQuery->whereHas('site', function ($q) use ($searchSite) {
                $q->where('name', 'like', "%{$searchSite}%");
            });
        }

        if ($search) {
            $overtimesQuery->where(function ($query) use ($search) {
                // cari di kolom Permit sendiri
                $query->where('demand', 'like', "%{$search}%")
                    ->orWhereHas('attendance.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // filter tanggal (pastikan ada kolom date di tabel overtime)
        if ($startDate && $endDate) {
            $overtimesQuery->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $overtimesQuery->whereDate('date', '>=', $startDate);
        } elseif ($endDate) {
            $overtimesQuery->whereDate('date', '<=', $endDate);
        }

        $overtimes = $overtimesQuery->get();

        return response()->json([
            'message' => 'Data ditemukan',
            'data'    => [
                'sites'  => $sites->pluck('name'),
                'overtimes' => $overtimes
            ]
        ]);
    }

    public function show(string $id)
    {
        $overtime = Overtime::where('id', $id)->first();
        $overtime->load([
            'attendance.user' => ['roles', 'leader.roles', 'leader.leader.roles'], //amobil relasi user beserta roles dan leader
            'site.company'
        ]);


        if (!$overtime) {
            return response()->json(['message' => 'Data lembur tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail lembur ditemukan.',
            'data' => $overtime
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $userEsign = Auth::user()->profile->esign ?? null; //ambil data esign dari profile user

        // cek apakah user sudah mengunggah tanda tangan elektronik
        if (empty($userEsign)) {
            return response()->json([
                'message' => 'Anda harus mengunggah tanda tangan elektronik terlebih dahulu.'
            ], 403);
        }
        // Cari data lembur berdasarkan ID
        $overtime = Overtime::findOrFail($id);


        $esignOld = json_decode($overtime->esign, true) ?? [];

        $userSign = [
            'user_id' => Auth::id(),
            'esign_user' => json_decode($userEsign, true)['esign_url'], //ambil esign_url dari JSON
        ];

        // membatasi user hanya bisa menandatangani sekali
        foreach ($esignOld as $sign) {
            if ($sign['user_id'] === Auth::id()) {
                return response()->json([
                    'message' => 'Anda sudah menandatangani lembur ini sebelumnya.'
                ], 400);
            }
        }
        $result = array_merge($esignOld, [$userSign]);
        $esignOvertime = json_encode($result); //masukan ke JSON esign lembur

        // Update status sesuai input form
        $overtime->status = $request->status;
        $overtime->esign = $esignOvertime;
        $overtime->save();

        // Bisa balikan response JSON atau redirect
        return response()->json([
            'message' => 'Status lembur berhasil diupdate',
            'data' => $overtime
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
