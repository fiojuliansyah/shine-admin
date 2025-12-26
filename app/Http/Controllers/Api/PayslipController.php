<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeneratePayroll;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{

    public function index()
    {
        $userId = Auth::id();

        $payroll = GeneratePayroll::with([
            'user.site.company',
            'user.roles' 
        ])
            ->where('user_id', $userId)
            ->latest()
            ->first();

        if (!$payroll) {
            return response()->json([
                'message' => 'Payslip tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Payslip terbaru berhasil ditemukan.',
            'payroll' => $payroll
        ], 200);
    }
}
