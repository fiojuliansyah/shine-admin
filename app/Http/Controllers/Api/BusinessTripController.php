<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Trip_progress;
use App\Models\Business_trips;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Intervention\Image\Colors\Rgb\Channels\Red;

class BusinessTripController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $businessTrips = Business_trips::with('trip_progress')->where('site_id', $user->site->id)
            ->get();
        $tripsPending = $businessTrips->where('user_id', $user->id)->where('status', 'pending')->where('approved_id', null);
        $tripsApproved = $businessTrips->where('user_id', $user->id)->where('status', 'approved');
        $tripsCompleted = $businessTrips->where('user_id', $user->id)->where('status', 'completed');
        $tripsProgress = $businessTrips->where('user_id', $user->id)->where('status', 'in_progress');

        return response()->json([
            'message' => "business trip successfully",
            'data' => [
                'trips_pending'      => $tripsPending->values(),
                'trips_approved'     => $tripsApproved->values(),
                'trips_completed'    => $tripsCompleted->values(),
                'trip_progress'      => $tripsProgress
            ]
        ]);
    }

    public function show($id)
    {
        $userId = Auth::id();

        $trip = Business_trips::with('trip_progress', 'user.site')
            ->where('user_id', $userId)
            ->findOrFail($id);

        return response()->json([
            'message' => 'Business trip details',
            'data' => $trip
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'purpose' => 'required|string',
            'information' => 'nullable|string',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);
        // jika ada images di upload
        if ($request->hasFile('images')) {
            try {
                $images = $request->file('images');
                $imagesUpload = []; // insialisasi array untuk menyimpan URL gambar yang diupload ke database
                foreach ($images as $image) {
                    $upload = Cloudinary::upload($image->getRealPath(), [
                        'folder' => 'business_trips',
                        'transformation' => [
                            'width' => 300,
                            'height' => 200,
                            'crop' => 'fill'
                        ]
                    ]);
                    $imagesUpload[] = [
                        'url' => $upload->getSecurePath(),
                        'image_public_id' => $upload->getPublicId()
                    ];
                }
            } catch (\Exception $e) {
                return response()->json(['message' => 'Image upload failed', 'error' => $e->getMessage()], 500);
            }

            // jika ada gambar maka upload
            $businessTrips = Business_trips::create([
                'site_id'      => Auth::user()->site->id,
                'user_id'      => Auth::user()->id,
                'title'        => $validated['title'],
                'purpose'      => $validated['purpose'],
                'information' => $validated['information'] ?? null,
                'images'       => json_encode($imagesUpload), // simpan array URL gambar sebagai JSON
                'status'       => 'pending'
            ]);
        } else {
            // jika tidka ada gambar di uplod
            $businessTrips = Business_trips::create([
                'site_id'      => Auth::user()->site->id,
                'user_id'      => Auth::user()->id,
                'title'        => $validated['title'],
                'purpose'      => $validated['purpose'],
                'information' => $validated['information'] ?? null,
                'status'       => 'pending'
            ]);
        }


        return response()->json([
            'message' => 'Business trip created successfully',
            'data'    => $businessTrips
        ], 201);
    }

    public function edit($id)
    {
        $userId = Auth::id();

        $trip = Business_trips::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$trip) {
            return response()->json(['message' => 'Business trip not found or access denied'], 404);
        }

        return response()->json([
            'message' => 'Edit business trip',
            'data' => $trip
        ]);
    }

    public function update(Request $request, $id)
    {
        $businessTrips = Business_trips::where('user_id', Auth::user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'purpose' => 'required|string',
            'information' => 'nullable|string',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('images')) {
            try {
                $images = $request->file('images');
                $imagesUpload = [];

                // hapus semua jika ada image lama
                $imgDecode = json_decode($businessTrips->images, true); // decode menjadi array assosiative
                if ($imgDecode) {
                    foreach ($imgDecode as $img) {
                        if (!empty($img['image_public_id'])) {
                            Cloudinary::destroy($img['image_public_id']);
                        }
                    }
                }

                // tambah kan img baru
                foreach ($images as $image) {
                    $upload = Cloudinary::upload($image->getRealPath(), [
                        'folder' => 'business_trips',
                        'transformation' => [
                            'width' => 300,
                            'height' => 200,
                            'crop' => 'fill'
                        ]
                    ]);
                    $imagesUpload[] = [
                        'url' => $upload->getSecurePath(),
                        'image_public_id' => $upload->getPublicId()
                    ];
                }

                $dataTrips = $businessTrips->update([
                    'title' => $validated['title'],
                    'purpose' => $validated['purpose'],
                    'information' => $validated['information'],
                    'images' => json_encode($imagesUpload),
                ]);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Image upload failed', 'error' => $e->getMessage()], 500);
            }
        }

        $dataTrips = $businessTrips->update([
            'title' => $validated['title'],
            'purpose' => $validated['purpose'],
            'information' => $validated['information'],
        ]);

        return response()->json([
            'message' => 'Business trip updated successfully',
            'businessTrips' => $dataTrips
        ]);
    }

    public function progressStart(Request $request)
    {
        $request->validate([
            'business_trip_id' => 'required|exists:business_trips,id', // cek apakah ada id businessTrips
        ]);

        $userId = Auth::id();
        $bussines = Business_trips::findOrfail($request->business_trip_id);
        $bussines->update(['status' => 'in_progress']);

        // tambah ke progress
        $progressTrip = Trip_progress::create([
            'business_trip_id' => $request->business_trip_id,
            'user_id' => $userId,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->toTimeString(),
            // 'latlong' => '',
        ]);

        return response()->json([
            'message' => "start progress successfully",
            'progressTrip' => $progressTrip
        ]);
    }
    public function progressEnd(Request $request)
    {
        $request->validate([
            'business_trip_id' => 'required|exists:Business_trips,id', // cek apakah ada id businessTrips
        ]);

        $userId = Auth::id();
        $bussines = Business_trips::findOrfail($request->business_trip_id);
        $bussines->update(['status' => 'completed']);
        // update ke selesai
        $progressTrip = Trip_progress::where('business_trip_id', $request->business_trip_id)
            ->where('user_id', $userId)
            ->first();

        if (!$progressTrip) {
            return response()->json([
                'message' => "start progress not found",
            ], 404);
        }

        // jika ada maka update
        $progressTrip->update([
            'end_time' => Carbon::now()->toTimeString(),
            // 'latlong' => '',
        ]);

        return response()->json([
            'message' => "End progress successfully",
            'progressTrip' => $progressTrip
        ]);
    }

    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'latlong' => 'required',
            'business_trip_id' => 'required'
        ]);

        $trip = Trip_progress::where('business_trip_id', $request->business_trip_id)->first();
        // return response()->json($trip);
        if ($trip) {
            // Ambil data lama
            $locations = json_decode($trip->latlong, true) ?? [];

            // Tambah data baru
            $locations[] = $request->latlong; // format: "-6.213...,108.3..."

            // Simpan lagi
            $trip->update([
                'latlong' => json_encode($locations)
            ]);
        }

        return response()->json([
            'data' => $locations
        ]); 
    }
}
