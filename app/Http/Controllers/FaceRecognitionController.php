<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FaceRecognitionController extends Controller
{
    public function registerFace(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'face_descriptor' => ['required', 'string'],
            'face_landmarks' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Invalid face data provided')
                ->withErrors($validator);
        }

        try {
            
            $faceDescriptor = json_decode($request->face_descriptor, true);
            $faceLandmarks = json_decode($request->face_landmarks, true);
            
            
            if (!is_array($faceDescriptor) || !is_array($faceLandmarks)) {
                return redirect()->back()
                    ->with('error', 'Invalid face data format');
            }

            
            $user = Auth::user();
            
            
            $profile = $user->profile;
            
            if (!$profile) {
                $profile = new Profile();
                $profile->user_id = $user->id;
            }
            
            
            $faceId = Str::uuid()->toString();
            
            
            $profile->face_id = $faceId;
            $profile->face_descriptor = json_encode($faceDescriptor);
            $profile->face_landmarks = json_encode($faceLandmarks);
            $profile->save();

            
            return redirect()->route('mobile.dashboard')
                ->with('success', 'Face registered successfully!');
                
        } catch (\Exception $e) {
            
            \Log::error('Face registration error: ' . $e->getMessage());
            
            
            return redirect()->back()
                ->with('error', 'An error occurred while registering your face: ' . $e->getMessage());
        }
    }
}