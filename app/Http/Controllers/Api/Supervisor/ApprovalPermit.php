<?php

namespace App\Http\Controllers\Api\Supervisor;

use App\Models\Permit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ApprovalPermit extends Controller
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

        // query Permit
        $permitsQuery = Permit::with([
            'site:id,name',
            'user:id,name,site_id' // ambil nama user dan site_id
        ])
            ->whereIn('site_id', $siteIds);

        // filter berdasarkan nama site (dari tabel site)
        if ($searchSite) {
            $permitsQuery->whereHas('site', function ($q) use ($searchSite) {
                $q->where('name', 'like', "%{$searchSite}%");
            });
        }

        if ($search) {
            $permitsQuery->where(function ($query) use ($search) {
                // cari di kolom Permit sendiri
                $query->where('title', 'like', "%{$search}%");

                // cari juga di relasi user
                $query->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }


        $permits = $permitsQuery->get();

        return response()->json([
            'message' => 'Data ditemukan',
            'data'    => [
                'sites'  => $sites->pluck('name'),
                'permits' => $permits
            ]
        ]);
    }

    public function show($id)
    {
        $permit = Permit::where('id', $id)->first();
        $permit->load(['user.roles', 'user.site', 'user.leader', 'user.leader.roles', 'user.leader.leader.roles', 'site.company']);

        if (!$permit) {
            return response()->json(['message' => 'Data izin tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail izin ditemukan.',
            'data' => $permit
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

        // Cari data izin berdasarkan ID
        $permit = Permit::findOrFail($id);


        $esignOld = json_decode($permit->esign, true) ?? [];

        $userSign = [
            'user_id' => Auth::id(),
            'esign_user' => json_decode($userEsign, true)['esign_url'], //ambil esign_url dari JSON
        ];

        // membatasi user hanya bisa menandatangani sekali
        foreach ($esignOld as $sign) {
            if ($sign['user_id'] === Auth::id()) {
                return response()->json([
                    'message' => 'Anda sudah menandatangani izin ini sebelumnya.'
                ], 400);
            }
        }
        $result = array_merge($esignOld, [$userSign]);
        $esignPermit = json_encode($result); //masukan ke JSON esign izin

        // Update status sesuai input form
        $permit->status = $request->status;
        $permit->esign = $esignPermit;
        $permit->save();

        // Bisa balikan response JSON atau redirect
        return response()->json([
            'message' => 'Status izin berhasil diupdate',
            'data' => $permit
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
