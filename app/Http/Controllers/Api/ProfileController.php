<?php

namespace App\Http\Controllers\Api;

use App\Models\Profile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar_url' => 'nullable|url',
            'avatar_public_id' => 'nullable|string|max:255',
            'avatar_encode' => 'nullable|string',
            'esign' => 'nullable|string|max:255',
            'employee_nik' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'mother_name' => 'nullable|string|max:255',
            'npwp_number' => 'nullable|string|max:255',
            'marriage_status' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'employee_status' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'resign_date' => 'nullable|date',
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',

            // tambahan
            'face_id' => 'nullable|string', // hasil descriptor wajah
            'face_image_data' => 'nullable|string', // base64 image wajah
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = Auth::id();
        $profile = Profile::firstOrCreate(['user_id' => $userId]);

        if (!$profile) {
            return response()->json(['message' => 'Profile not found.'], 404);
        }

        $data = $validator->validated();

        // Upload gambar wajah ke Cloudinary jika ada base64 image
        if (!empty($data['face_image_data'])) {
            try {
                $upload = Cloudinary::upload($data['face_image_data'], [
                    'folder' => 'faces',
                    'public_id' => 'face_' . $userId,
                    'overwrite' => true,
                    'format' => 'jpg',
                ]);

                $data['avatar_url'] = $upload->getSecurePath();
                $data['avatar_public_id'] = $upload->getPublicId();
            } catch (\Exception $e) {
                return response()->json(['message' => 'Gagal upload gambar wajah ke Cloudinary.', 'error' => $e->getMessage()], 500);
            }
        }

        // Simpan face_id (face descriptor)
        if (!empty($data['face_id'])) {
            $data['face_descriptor'] = $data['face_id'];
            unset($data['face_id']); // tidak perlu disimpan ke DB
        }

        // Update profile
        $profile->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => $profile,
        ], 200);
    }

    public function show()
    {
        $profile = Profile::where('user_id', Auth::id())->first();

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Profile fetched successfully.',
            'profile' => $profile
        ], 200);
    }

    public function updateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'nullable|string|max:255',
            'email'    => 'nullable|email|max:255',
            'nik'      => 'nullable|string|max:50',
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $data = $validator->validated();

        // Hash password kalau ada
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Account updated successfully.',
            'user'    => $user->fresh(),
        ], 200);
    }

    public function esign()
    {
        $user = Auth::user()->load('profile');
        $esign = json_decode($user['profile']['esign'] ?? null, true) ;
        $signature = $esign['esign_url'] ?? null;

        if (!$signature) {
            return response()->json([
                'message' => 'No e-signature found.',
                'signature' => null
            ], 404);
        }

        return response()->json([
            'message' => 'E-signature fetched successfully.',
            'user' =>$user,
            'signature' => $signature
        ], 200);
    }

    public function updateEsign(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile()->first();

        if ($request->esign) {
            $imageData = $request->input('esign');

            list($type, $imageData) = explode(';', $imageData);
            list(, $imageData)      = explode(',', $imageData);

            $imageData = 'data:image/png;base64,' . $imageData;

            $cloudinaryImageIn = Cloudinary::upload($imageData, [
                'folder' => 'esign_images'
            ]);

            $esignUrl = $cloudinaryImageIn->getSecurePath();
            $esignPublicId = $cloudinaryImageIn->getPublicId();

            if ($profile) {
                $esignProfile = json_decode($profile->esign, true);
                if ($esignProfile && isset($esignProfile['esign_public_id'])) {
                    // Hapus esign lama dari Cloudinary
                    $esignProfile['esign_url'] = $esignUrl;
                    $esignProfile['esign_public_id'] = $esignPublicId;
                    $esign = json_encode($esignProfile);
                }
                $profile->esign = $esign;
                $profile->save();
            } else {
                $esign = json_encode([
                    'esign_url' => $esignUrl,
                    'esign_public_id' => $esignPublicId
                ]);
                $user->profile()->create([
                    'esign' => $esign
                ]);
            }
        }else{
            return response()->json([
                'message' => 'No e-signature data provided.'
            ], 400);
        }

        return response()->json([
            'message' => 'E-signature updated successfully.',
            'profile' => $user->profile()->first()
        ], 200);
    }
}
