<?php

namespace App\Http\Controllers\Api\Supervisor;

use App\Models\Leave;
use App\Models\Permit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ApprovalLeave extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil parameter search
        $searchSite  = $request->query('site');    // ?site=Tim Pengembang
        $search = $request->query('search');  // ?search=Andi

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

        // query leave
        $leavesQuery = Leave::with([
            'type:id,name',
            'site:id,name',
            'user:id,name,site_id' // ambil nama user dan site_id
        ])
            ->whereIn('site_id', $siteIds);

        // filter berdasarkan nama site (dari tabel site)
        if ($searchSite) {
            $leavesQuery->whereHas('site', function ($q) use ($searchSite) {
                $q->where('name', 'like', "%{$searchSite}%");
            });
        }

        if ($search) {
            $leavesQuery->where(function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('type', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $leaves = $leavesQuery->get();

        return response()->json([
            'message' => 'Data ditemukan',
            'data'    => [
                'sites'  => $sites->pluck('name'),
                'leaves' => $leaves
            ]
        ]);
    }

    public function show($id)
    {
        $leave = Leave::where('id', $id)->first();
        $leave->load(['user.roles', 'user.site', 'user.leader', 'user.leader.roles', 'user.leader.leader.roles', 'site.company']);

        if (!$leave) {
            return response()->json(['message' => 'Data cuti tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail cuti ditemukan.',
            'data' => $leave
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
        // Cari data cuti berdasarkan ID
        $leave = Leave::findOrFail($id);

        $esignOld = json_decode($leave->esign, true) ?? [];

        $userSign = [
            'user_id' => Auth::id(),
            'esign_user' => json_decode($userEsign, true)['esign_url'], //ambil esign_url dari JSON
        ];
        
        // membatasi user hanya bisa menandatangani sekali
        foreach ($esignOld as $sign) {
            if ($sign['user_id'] === Auth::id()) {
                return response()->json([
                    'message' => 'Anda sudah menandatangani cuti ini sebelumnya.'
                ], 400);
            }
        }
        $result = array_merge($esignOld, [$userSign]);
        $esignLeave = json_encode($result); //masukan ke JSON esign cuti

        // Update status sesuai input form
        $leave->status = $request->status;
        $leave->esign = $esignLeave;
        $leave->save();

        // Bisa balikan response JSON atau redirect
        return response()->json([
            'message' => 'Status cuti berhasil diupdate',
            'data' => $leave
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
