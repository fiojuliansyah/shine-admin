<?php

namespace App\Http\Controllers\Api;

use App\Models\Profile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\FileStorage;
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

        // Upload gambar wajah ke storage jika ada base64 image
        if (!empty($data['face_image_data'])) {
            try {
                $upload = FileStorage::uploadBase64($data['face_image_data'], 'faces', 'face_' . $userId . '_' . time());
            } catch (\Exception $e) {
                return response()->json(['message' => 'Gagal upload gambar wajah ke storage.', 'error' => $e->getMessage()], 500);
            }

            // Gantikan foto wajah lama (sebelumnya di-overwrite di Cloudinary)
            if (str_starts_with((string) $profile->avatar_public_id, 'faces/')) {
                FileStorage::delete($profile->avatar_public_id);
            }

            $data['avatar_url'] = $upload['url'];
            $data['avatar_public_id'] = $upload['path'];
        }

        // Base64 mentah tidak disimpan ke DB (kolomnya memang tidak ada) — file sudah di storage
        unset($data['face_image_data']);

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
            'nik'      => 'nullable|digits:16',
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
        ], ['nik.digits' => 'NIK KTP wajib 16 digit angka.']);

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

            // Paksa format PNG, sama seperti sebelumnya
            $base64 = str_contains($imageData, ',') ? substr($imageData, strpos($imageData, ',') + 1) : $imageData;

            try {
                $upload = FileStorage::uploadBase64('data:image/png;base64,' . $base64, 'esign_images');
            } catch (\Exception $e) {
                return response()->json(['message' => 'Gagal upload e-signature ke storage.', 'error' => $e->getMessage()], 500);
            }

            $esign = json_encode([
                'esign_url' => $upload['url'],
                'esign_public_id' => $upload['path'],
            ]);

            if ($profile) {
                // Hapus file esign lama (jika sebelumnya tersimpan sebagai file)
                $oldEsign = json_decode((string) $profile->esign, true);
                if (is_array($oldEsign) && !empty($oldEsign['esign_public_id'])) {
                    FileStorage::delete($oldEsign['esign_public_id']);
                }

                $profile->esign = $esign;
                $profile->save();
            } else {
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
