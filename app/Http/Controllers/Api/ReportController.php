<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\FindingsReport;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ReportController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $reports = FindingsReport::where('user_id', $userId)
            ->where('is_work_assignments', false)
            ->orderBy('date', 'desc')
            ->latest()->get();

        return response()->json([
            'message' => 'Daftar laporan ditemukan.',
            'data' => $reports
        ]);
    }

    public function show($id)
    {
        $report = FindingsReport::with('site.company', 'user.site', 'user.roles')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
        if (!$report) {
            return response()->json(['message' => 'Data laporan tidak ditemukan'], 400);
        }

        return response()->json([
            "message" => "Data Laporan ditemukan",
            "data" => $report
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'direct_action' => 'required|string',
            'status' => 'required|in:pending,solved',
            'type' => 'required|in:low,medium,high',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = Auth::user();
        $imgUrl = null;
        $imgPublicId = null;

        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                $upload = Cloudinary::upload($image->getRealPath(), [
                    'folder' => 'reports',
                ]);
                $imgUrl = $upload->getSecurePath();
                $imgPublicId = $upload->getPublicId();
            } catch (\Exception $e) {
                return response()->json(['message' => 'Gagal upload gambar.'], 500);
            }
        }

        // menambhakn tanggal saat ini
        $dateNow = Carbon::now()->toDateString();

        $report = FindingsReport::create([
            'user_id'         => $user->id,
            'site_id'         => $user->site_id,
            'title'           => $request->title,
            'date'             => $dateNow,
            'description'        => $request->description,
            'location'        => $request->location,
            'direct_action'          => $request->direct_action,
            'status'         => $request->status,
            'type'         => $request->type,
            'image_url'       => $imgUrl,
            'image_public_id' => $imgPublicId
        ]);

        return response()->json([
            'message' => 'Laporan berhasil diajukan.',
            'data' => $report
        ], 200);
    }

    public function edit($id)
    {
        $report = FindingsReport::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
        if (!$report) {
            return response()->json(['message' => 'Data laporan tidak ditemukan'], 400);
        }

        return response()->json([
            "message" => "Data Laporan ditemukan",
            "data" => $report
        ]);
    }

    public function update(Request $request, $id)
    {
        $report = FindingsReport::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'direct_action' => 'required|string',
            'status' => 'required|in:pending,solved',
            'type' => 'required|in:low,medium,high',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('image')) {
            try {
                if (!empty($report->image_public_id)) {
                    Cloudinary::destroy($report->image_public_id);
                }

                $image = $request->file('image');
                $upload = Cloudinary::upload($image->getRealPath(), [
                    'folder' => 'reports',
                ]);

                // update
                $image_url = $report->image_url = $upload->getSecurePath();
                $image_public_id = $report->image_public_id = $upload->getPublicId();
                $report->update([
                    'title' => $request->title,
                    'description' => $request->description,
                    'location' => $request->location,
                    'direct_action' => $request->direct_action,
                    'status' => $request->status,
                    'type' => $request->type,
                    'image_url' => $image_url,
                    'image_public_id' => $image_public_id
                ]);
                return response()->json(['message' => $report]);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Gagal upload gambar.'], 500);
            }
        }

        $report->update([
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'direct_action' => $request->direct_action,
            'status' => $request->status,
            'type' => $request->type,
        ]);

        return response()->json(['message' => $report]);
    }
}
