<?php

namespace App\Http\Controllers\Api\Supervisor;

use App\Models\Minute;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ApprovalMinute extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $searchSite = $request->query('site');
        $search = $request->query('search');

        $sites = $user->sites_leader;
        $siteIds = $sites->pluck('id');

        if ($siteIds->isEmpty()) {
            return response()->json([
                'message' => 'No sites found',
                'data' => []
            ], 404);
        }

        $minutesQuery = Minute::with(['attendance'=>function($att) use ($siteIds){
            $att->whereIn('site_id', $siteIds);
        },'attendance.user:id,name', 'attendance.site:id,name']);

        if($searchSite){
            $minutesQuery->whereHas('attendance.site', function($q) use ($searchSite){
                $q->where('name', 'like', "%{$searchSite}%");
            });
        }

        if($search){
            $minutesQuery->where('remark', 'like', "%{$search}%")
                ->orWhereHas('attendance.user', function($q) use ($search){
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        $minutes = $minutesQuery->get();

        return response()->json([
            'message' => 'List of minutes pending approval',
            'data' => [
                'sites' => $sites->pluck('name'),
                'minutes' => $minutes 
            ]
        ]);
    }

    public function show($id)
    {
        $minute = Minute::where('id', $id)->first();
        $minute->load([
            'attendance.user' => ['roles', 'leader.roles', 'leader.leader.roles'], //amobil relasi user beserta roles dan leader
            'attendance.site.company'
        ]);


        if (!$minute) {
            return response()->json(['message' => 'Data berita acara tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail berita acara ditemukan.',
            'data' => $minute
        ]);
    }

    public function update(Request $request, $id)
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
        // Cari data berita acara berdasarkan ID
        $minute = Minute::findOrFail($id);

        $esignOld = json_decode($minute->esign, true) ?? [];

        $userSign = [
            'user_id' => Auth::id(),
            'esign_user' => json_decode($userEsign, true)['esign_url'], //ambil esign_url dari JSON
        ];
        
        // membatasi user hanya bisa menandatangani sekali
        foreach ($esignOld as $sign) {
            if ($sign['user_id'] === Auth::id()) {
                return response()->json([
                    'message' => 'Anda sudah menandatangani berita acara ini sebelumnya.'
                ], 400);
            }
        }
        $result = array_merge($esignOld, [$userSign]);
        $esignMinute = json_encode($result); //masukan ke JSON esign berita acara

        // Update status sesuai input form
        $minute->status = $request->status;
        $minute->esign = $esignMinute;
        $minute->save();

        // Bisa balikan response JSON atau redirect
        return response()->json([
            'message' => 'Status berita acara berhasil diupdate',
            'data' => $minute
        ]);
    }
}
