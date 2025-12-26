<?php

namespace App\Http\Controllers;

use DataTables;
use Carbon\Carbon;
use App\Models\Site;
use App\Models\User;
use App\Models\Minute;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\DataTables\MinutesDataTable;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MinuteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(MinutesDataTable $dataTable)
    {
        return $dataTable->render('attendances.minutes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:clockin,clockout',
            'remark' => 'required|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $today = Carbon::now()->format('Y-m-d');
        $userId = Auth::user()->id;
        $attendance = Attendance::firstOrCreate(['date' => $today], ['date' => $today]);

        $imgUrl = $request->hasFile('image') ? $request->file('image')->storeOnCloudinary('minutes')->getSecurePath() : null;
        $imgPublicId = $request->hasFile('image') ? $request->file('image')->storeOnCloudinary('minutes')->getPublicId() : null;

        if ($request->type === 'clockin') {
            $attendance->update([
                'clock_in' => Carbon::now()->format('H:i'),
                'user_id' => $userId,
                'imagein_url' => $imgUrl,
                'imagein_public_id' => $imgPublicId,
            ]);

            Minute::create([
                'attendance_id' => $attendance->id,
                'type' => 'clockin',
                'remark' => $request->remark,
                'image_url' => $imgUrl,
                'image_public_id' => $imgPublicId,
            ]);
        } elseif ($request->type === 'clockout') {
            $attendance->update([
                'clock_out' => Carbon::now()->format('H:i'),
                'user_id' => $userId,
                'imageout_url' => $imgUrl,
                'imageout_public_id' => $imgPublicId,
            ]);

            Minute::create([
                'attendance_id' => $attendance->id,
                'type' => 'clockout',
                'remark' => $request->remark,
                'image_url' => $imgUrl,
                'image_public_id' => $imgPublicId,
            ]);
        }

        return redirect()->route('minutes.index')->with('success', 'Minute recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Minute $minute)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Minute $minute)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $minute = Minute::findOrFail($id);
        $attendance = $minute->attendance;

        $request->validate([
            'type' => 'required|string|in:clockin,clockout',
            'remark' => 'required|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = [
            'type' => $request->type,
            'remark' => $request->remark,
        ];

        if ($request->hasFile('image')) {
            if ($minute->image_public_id) {
                Cloudinary::destroy($minute->image_public_id);
            }

            $cloudinaryImage = $request->file('image')->storeOnCloudinary('minutes');
            $data['image_url'] = $cloudinaryImage->getSecurePath();
            $data['image_public_id'] = $cloudinaryImage->getPublicId();
        }

        $minute->update($data);

        if ($request->type === 'clockin') {
            $attendance->update(['clock_in' => $minute->created_at->format('H:i')]);
        } elseif ($request->type === 'clockout') {
            $attendance->update(['clock_out' => $minute->created_at->format('H:i')]);
        }

        return redirect()->route('minutes.index')->with('success', 'Minute updated successfully.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $minute = Minute::findOrFail($id);
        $attendance = $minute->attendance;

        if ($minute->image_public_id) {
            Cloudinary::destroy($minute->image_public_id);
        }

        $minute->delete();

        if ($minute->type === 'clockin') {
            $attendance->update(['clock_in' => null]);
        } elseif ($minute->type === 'clockout') {
            $attendance->update(['clock_out' => null]);
        }

        return redirect()->route('minutes.index')->with('success', 'Minute deleted successfully.');
    }
}
