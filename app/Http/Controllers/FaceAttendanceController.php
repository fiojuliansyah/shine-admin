<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use App\Models\Profile; 
use App\Models\Attendance;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;

class FaceAttendanceController extends Controller
{
    const FACE_MATCH_THRESHOLD = 0.6; 
    const STANDARD_CLOCK_IN = '08:00:00';

    public function showFaceRegisterForm()
    {
        $sites = Site::all();
        return view('website.face-account-register', compact('sites'));
    }

    public function storeAccountAndFace(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'nik' => 'nullable|string|max:20',
            'employee_nik' => 'nullable|string|max:50|unique:users,employee_nik',
            'phone' => 'nullable|string|max:15',
            'site_id' => 'nullable|numeric',
            'leader_id' => 'nullable|numeric',
            'department_id' => 'nullable|numeric',
            'is_employee' => 'nullable|in:0,1',

            'face_descriptor' => 'required|json',
            'image' => 'nullable|string',
            'avatar' => 'nullable|file|image|max:5000',

            'address' => 'nullable|string',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'mother_name' => 'nullable|string|max:255',
            'number_of_children' => 'nullable|integer',
            'npwp_number' => 'nullable|string|max:30',
            'marriage_status' => 'nullable|string|max:10',
            'join_date' => 'nullable|date',
            'resign_date' => 'nullable|date|after_or_equal:join_date',
            'bank_name' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'nik' => $request->nik,
                'employee_nik' => $request->employee_nik,
                'phone' => $request->phone,
                'site_id' => $request->site_id,
                'leader_id' => $request->leader_id,
                'department_id' => $request->department_id,
                'is_employee' => $request->is_employee,
            ]);

            $data = [
                'gender' => $request->gender,
                'birth_place' => $request->birth_place,
                'birth_date' => $request->birth_date,
                'mother_name' => $request->mother_name,
                'number_of_children' => $request->number_of_children,
                'npwp_number' => $request->npwp_number,
                'marriage_status' => $request->marriage_status,
                'address' => $request->address,
                'join_date' => $request->join_date ?? Carbon::now(),
                'resign_date' => $request->resign_date,
                'bank_name' => $request->bank_name,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'avatar_url' => null,
                'avatar_public_id' => null,
                'face_descriptor' => null,
                'face_id' => null,
            ];

            try {
                if ($request->hasFile('avatar')) {
                    $imageFile = $request->file('avatar');

                    $cloudinaryImage = Cloudinary::upload($imageFile->getRealPath(), [
                        'folder' => 'avatars',
                        'format' => 'jpg',
                        'transformation' => [
                            'width' => 400,
                            'height' => 400,
                            'crop' => 'fill',
                            'gravity' => 'face',
                            'quality' => 75
                        ]
                    ]);

                    $url = $cloudinaryImage->getSecurePath();
                    $public_id = $cloudinaryImage->getPublicId();

                    $data['avatar_url'] = $url;
                    $data['avatar_public_id'] = $public_id;

                    Log::info('Avatar uploaded directly from file', ['user_id' => $user->id]);
                } elseif ($request->filled('image')) {
                    $base64_str = $request->image;

                    if (strpos($base64_str, ';base64,') !== false) {
                        $base64_str = explode(';base64,', $base64_str)[1];
                    } elseif (strpos($base64_str, ',') !== false) {
                        $base64_str = explode(',', $base64_str)[1];
                    }

                    if (base64_encode(base64_decode($base64_str, true)) === $base64_str) {
                        $imageData = base64_decode($base64_str);

                        $tempFilePath = sys_get_temp_dir() . '/' . uniqid('face_') . '.jpg';
                        file_put_contents($tempFilePath, $imageData);

                        $cloudinaryImage = Cloudinary::upload($tempFilePath, [
                            'folder' => 'avatars',
                            'format' => 'jpg',
                            'transformation' => [
                                'width' => 400,
                                'height' => 400,
                                'crop' => 'limit',
                                'quality' => 75
                            ]
                        ]);

                        if (file_exists($tempFilePath)) {
                            unlink($tempFilePath);
                        }

                        $url = $cloudinaryImage->getSecurePath();
                        $public_id = $cloudinaryImage->getPublicId();

                        $data['avatar_url'] = $url;
                        $data['avatar_public_id'] = $public_id;

                        Log::info('Face image uploaded as avatar from base64', ['user_id' => $user->id]);
                    } else {
                        Log::error('Invalid base64 string for face image', ['user_id' => $user->id]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error uploading image', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
            }

            if ($request->filled('face_descriptor')) {
                try {
                    $faceId = (string) Str::uuid();

                    $data['face_id'] = $faceId;
                    $data['face_descriptor'] = $request->face_descriptor;

                    Log::info('Face ID processed successfully', ['user_id' => $user->id]);
                } catch (\Exception $e) {
                    Log::error('Error processing face ID', ['error' => $e->getMessage(), 'user_id' => $user->id]);
                }
            }

            $user->profile()->create($data);

            DB::commit();

            return back()->with('success', 'Registrasi akun dan Face ID berhasil disimpan!');


        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Registrasi gagal: ' . $e->getMessage());
        }
    }

    public function showAttendanceForm()
    {
        $users = User::select('id', 'name', 'employee_nik')->get();
        
        return view('website.face-attendance', compact('users'));
    }

    public function processFaceAttendance(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'employee_nik' => 'required|string|max:50',
            'face_descriptor' => 'required|json',
            'image' => 'required|string',
            'latlong' => 'required|string',
            'mode' => 'required|in:clockin,clockout',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return response()->json(['error' => 'Validasi Gagal: ' . $firstError], 422);
        }

        $inputDescriptor = json_decode($request->face_descriptor);
        $today = Carbon::today();
        $matchedUser = null;
        $mode = $request->mode;

        try {
            DB::beginTransaction();
            
            $allProfiles = Profile::whereNotNull('face_descriptor')->get();
            $minDistance = self::FACE_MATCH_THRESHOLD;
            
            foreach ($allProfiles as $profile) {
                $storedDescriptor = json_decode($profile->face_descriptor);
                
                if (!is_array($storedDescriptor)) continue;

                $distance = self::faceapi_get_distance($inputDescriptor, $storedDescriptor);

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $matchedUser = $profile->user; 
                }
            }

            if (!$matchedUser) {
                return response()->json(['error' => 'Wajah tidak cocok dengan user manapun yang terdaftar. Jarak minimum: ' . round($minDistance, 3)], 401);
            }

            $user = $matchedUser;

            $attendance = Attendance::where('user_id', $user->id)
                                    ->where('date', $today)
                                    ->first();

            if ($mode === 'clockin') {
                if ($attendance) {
                     return response()->json(['error' => "Absensi gagal. {$user->name} sudah Clock In hari ini. Silakan Clock Out."], 200); 
                }
                
                $cloudinaryData = $this->uploadFaceImage($request->image, 'clockin', $user->id);
                $clockInTime = Carbon::now();
                $standardTime = Carbon::parse(self::STANDARD_CLOCK_IN);
                
                $lateDuration = $clockInTime->gt($standardTime) 
                    ? $clockInTime->diffInMinutes($standardTime) 
                    : 0;

                $type = $lateDuration > 0 ? 'late' : 'regular';

                Attendance::create([
                    'user_id' => $user->id,
                    'site_id' => $user->site_id,
                    'date' => $today,
                    'latlong' => $request->latlong,
                    'clock_in' => $clockInTime->toTimeString(),
                    'face_image_url_clockin' => $cloudinaryData['url'],
                    'face_image_public_id_clockin' => $cloudinaryData['public_id'],
                    'late_duration' => $lateDuration,
                    'type' => $type,
                ]);

                $message = "Clock In sukses untuk {$user->name} pada {$clockInTime->format('H:i:s')}.";

            } elseif ($mode === 'clockout') {
                if (!$attendance) {
                     return response()->json(['error' => "Absensi gagal. {$user->name} belum Clock In hari ini."], 400); 
                }
                if ($attendance->clock_out) {
                     return response()->json(['error' => "Absensi gagal. {$user->name} sudah Clock Out hari ini."], 200); 
                }
                
                $cloudinaryData = $this->uploadFaceImage($request->image, 'clockout', $user->id);
                $clockOutTime = Carbon::now();

                $attendance->update([
                    'clock_out' => $clockOutTime->toTimeString(),
                    'face_image_url_clockout' => $cloudinaryData['url'],
                    'face_image_public_id_clockout' => $cloudinaryData['public_id'],
                ]);

                $message = "Clock Out sukses untuk {$user->name} pada {$clockOutTime->format('H:i:s')}.";
            } else {
                return response()->json(['error' => 'Mode aksi tidak valid.'], 400);
            }
            
            DB::commit();

            return response()->json(['success' => $message, 'nik' => $user->employee_nik, 'name' => $user->name], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Absensi gagal diproses karena kesalahan sistem.'], 500);
        }
    }

    protected static function faceapi_get_distance(array $descriptor1, array $descriptor2): float
    {
        if (count($descriptor1) !== count($descriptor2)) {
            throw new \InvalidArgumentException('Descriptors must have the same length.');
        }
        $sum = 0;
        foreach ($descriptor1 as $i => $value) {
            $sum += pow($value - $descriptor2[$i], 2);
        }
        return sqrt($sum);
    }

    protected function uploadFaceImage(string $base64Image, string $type, int $userId): array
    {
        $base64_str = $base64Image;

        if (strpos($base64_str, ';base64,') !== false) {
            $base64_str = explode(';base64,', $base64_str)[1];
        } elseif (strpos($base64_str, ',') !== false) {
            $base64_str = explode(',', $base64_str)[1];
        }

        if (base64_encode(base64_decode($base64_str, true)) !== $base64_str) {
             throw new \Exception('Invalid base64 string provided.');
        }

        $imageData = base64_decode($base64_str);

        $tempFilePath = sys_get_temp_dir() . '/' . uniqid("attendance_{$type}_") . '.jpg';
        file_put_contents($tempFilePath, $imageData);

        $cloudinaryImage = Cloudinary::upload($tempFilePath, [
            'folder' => 'attendance_faces',
            'format' => 'jpg',
            'public_id' => "user_{$userId}_{$type}_" . Carbon::now()->format('YmdHi'),
            'transformation' => [
                'width' => 400,
                'height' => 400,
                'crop' => 'limit',
                'quality' => 80
            ]
        ]);

        if (file_exists($tempFilePath)) {
            unlink($tempFilePath);
        }

        return [
            'url' => $cloudinaryImage->getSecurePath(),
            'public_id' => $cloudinaryImage->getPublicId(),
        ];
    }
}