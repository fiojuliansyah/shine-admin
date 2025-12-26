<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Leave;
use App\Models\TypeLeave;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = Leave::with('type')
            ->where('user_id', Auth::id())->get();

        return response()->json([
            'message' => 'Daftar cuti berhasil diambil.',
            'data' => $leaves,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $types = TypeLeave::where('site_id', $user->site_id)->get();

        return response()->json([
            'message' => 'Data type cuti berhasil diambil.',
            'data' => $types
        ]);
    }


    public function show($id)
    {
        $leave = Leave::with('type', 'site.company', 'user.roles', 'user.site', 'user.leader.roles', 'user.leader.leader.roles')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$leave) {
            return response()->json(['message' => 'Data cuti tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail cuti ditemukan.',
            'data' => $leave,
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'type_id'     => 'required|exists:type_leaves,id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string',
            'contact'     => 'nullable|string',
            'image'       => 'nullable|string'
        ]);

        $user = Auth::user();
        $imageUrl = null;
        $imagePublicId = null;

        if ($request->filled('image')) {
            try {
                $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
                $imageData = base64_decode($base64);
                $tempFile = tempnam(sys_get_temp_dir(), 'leave_');
                file_put_contents($tempFile, $imageData);

                $upload = Cloudinary::upload($tempFile, [
                    'folder' => 'leaves_images',
                    'transformation' => [
                        'width' => 300,
                        'height' => 200,
                        'crop' => 'fill',
                    ]
                ]);
                unlink($tempFile);

                $imageUrl = $upload->getSecurePath();
                $imagePublicId = $upload->getPublicId();
            } catch (\Exception $e) {
                Log::error('Upload image gagal: ' . $e->getMessage());
                return response()->json(['message' => 'Upload gambar gagal.'], 500);
            }
        }

        $attendance = Attendance::create([
            'date'    => $request->start_date,
            'user_id' => $user->id,
            'site_id' => $user->site_id,
            'type'    => 'leave',
            'clock_in' => Carbon::now(),
            'clock_out' => Carbon::now()
        ]);

        $leave = Leave::create([
            'user_id'        => $user->id,
            'site_id'        => $user->site_id,
            'type_id'        => $request->type_id,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'reason'         => $request->reason,
            'contact'        => $request->contact,
            'attendance_id'  => $attendance->id,
            'image_url'      => $imageUrl,
            'image_public_id' => $imagePublicId,
        ]);

        return response()->json([
            'message' => 'Pengajuan cuti berhasil.',
            'data' => $leave,
        ]);
    }

    public function update(Request $request, $id)
    {
        $leave = Leave::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

            $request->validate([
                'start_date' => 'nullable|date',
                'end_date'   => 'nullable|date|after_or_equal:start_date',
                'reason'     => 'nullable|string',
                'contact'    => 'nullable|string',
                'image'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

        if ($request->hasFile('image')) {
            try {
                if (!empty($leave->image_public_id)) {
                    Cloudinary::destroy($leave->image_public_id);
                }

                $image = $request->file('image');
                $upload = Cloudinary::upload($image->getRealPath(), [
                    'folder' => 'leaves',
                    'transformation' => [
                        'width' => 300,
                        'height' => 200,
                        'crop' => 'fill',
                    ]
                ]);
                $leave->image_url = $upload->getSecurePath();
                $leave->image_public_id = $upload->getPublicId();
            } catch (\Exception $e) {
                Log::error('Gagal update gambar leave$leave: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal upload gambar.'], 500);
            }
        }

            $leave->update($request->only(['start_date', 'end_date', 'reason', 'contact']));

        return response()->json([
            'message' => 'Data cuti berhasil diperbarui.',
            'data' => $leave
        ]);
    }
}
