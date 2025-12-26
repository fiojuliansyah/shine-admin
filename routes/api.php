<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\MinuteController;
use App\Http\Controllers\Api\PermitController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\PayslipController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReliverController;
use App\Http\Controllers\Api\OvertimeController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\Api\BusinessTripController;
use App\Http\Controllers\Api\SecurityPatrollController;
use App\Http\Controllers\Api\Supervisor\ApprovalLeave;
use App\Http\Controllers\Api\Supervisor\ApprovalMinute;
use App\Http\Controllers\Api\Supervisor\ApprovalOvertime;
use App\Http\Controllers\Api\Supervisor\ApprovalPermit;
use App\Http\Controllers\Api\Supervisor\ManageTimController;
use App\Http\Controllers\Api\Supervisor\ReportPatroll;
use App\Http\Controllers\Api\Supervisor\SwitchSheduleController;

Route::get('login', [AuthController::class, 'loginForm']);
Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/logs', [HomeController::class, 'logs']);

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/account', [ProfileController::class, 'updateAccount']);
        Route::get('/esign', [ProfileController::class, 'esign']);
        Route::post('/updateEsign', [ProfileController::class, 'updateEsign']);
    });

    // Attendance
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index']);
        Route::get('/clock-in-page', [AttendanceController::class, 'clockInPage']);
        Route::get('/clock-out-page', [AttendanceController::class, 'clockOutPage']);
        Route::post('/clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('/clock-out', [AttendanceController::class, 'clockOut']);
        Route::get('/status', [AttendanceController::class, 'status']);
        Route::post('/time-off', [AttendanceController::class, 'timeOff']);
    });

    //Minute
    Route::prefix('minute')->group(function () {
        Route::get('/', [MinuteController::class, 'index']);
        Route::post('/', [MinuteController::class, 'store']);
        Route::get('/{id}', [MinuteController::class, 'show']);
    });

    // Overtime
    Route::prefix('overtime')->group(function () {
        Route::get('/', [OvertimeController::class, 'index']);
        Route::post('/clock-in', [OvertimeController::class, 'storeClockIn']);
        Route::post('/clock-out', [OvertimeController::class, 'storeClockOut']);
    });

    // Permit
    Route::prefix('permit')->group(function () {
        Route::get('/', [PermitController::class, 'index']);
        Route::post('/', [PermitController::class, 'store']);
        Route::get('/{id}', [PermitController::class, 'show']);
        Route::post('/{id}', [PermitController::class, 'update']);
    });

    // Leave
    Route::prefix('leave')->group(function () {
        Route::get('/', [LeaveController::class, 'index']);
        Route::get('/create', [LeaveController::class, 'create']);
        Route::post('/', [LeaveController::class, 'store']);
        Route::get('/{id}', [LeaveController::class, 'show']);
        Route::post('/{id}', [LeaveController::class, 'update']);
    });

    // Reliver
    Route::prefix('reliver')->group(function () {
        Route::get('/', [ReliverController::class, 'index']);
        Route::post('/clock-in', [ReliverController::class, 'storeClockIn']);
        Route::post('/clock-out', [ReliverController::class, 'storeClockOut']);
    });

    // PaySlip
    Route::prefix('payslip')->group(function () {
        Route::get('/', [PayslipController::class, 'index']);
    });

    // Dinas
    Route::prefix('business-trip')->group(function () {
        Route::post('/updateLocation', [BusinessTripController::class, 'updateLocation']);
        Route::post('/progressStart', [BusinessTripController::class, 'progressStart']);
        Route::post('/progressEnd', [BusinessTripController::class, 'progressEnd']);
        Route::get('/', [BusinessTripController::class, 'index']);
        Route::get('/{id}', [BusinessTripController::class, 'show']);
        Route::get('/{id}/edit', [BusinessTripController::class, 'edit']);
        Route::post('/', [BusinessTripController::class, 'store']);
        Route::post('/{id}', [BusinessTripController::class, 'update']);
    });

    // Schedule
    Route::prefix('schedule')->group(function () {
        Route::get('/', [ScheduleController::class, 'index']);
        Route::get('/{id}', [ScheduleController::class, 'show']);
        Route::post('/', [ScheduleController::class, 'store']);
        Route::post('/start', [ScheduleController::class, 'progressStart']);
        Route::post('/end', [ScheduleController::class, 'progressEnd']);
    });

    // Report Temuan
    Route::prefix('report')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('/{id}', [ReportController::class, 'show']);
        Route::get('/{id}/edit', [ReportController::class, 'edit']);
        Route::post('/', [ReportController::class, 'store']);
    });

    // Security Patroll
    Route::prefix('security-patroll')->group(function () {
        Route::get('/', [SecurityPatrollController::class, 'index']);
        Route::post('/startPatroll', [SecurityPatrollController::class, 'startPatroll'])->name('securty-patroll.startPatroll');
        Route::post('/endPatroll', [SecurityPatrollController::class, 'endPatroll'])->name('securty-patroll.endPatroll');
        Route::get('/{id}', [SecurityPatrollController::class, 'listTask']);
        Route::get('/{id}/show', [SecurityPatrollController::class, 'show']);
        Route::get('/{id}/edit_create', [SecurityPatrollController::class, 'edit_create']);
        Route::post('/{id}', [SecurityPatrollController::class, 'updateOrCreate']);
    });

    Route::prefix('supervisor')->group(function(){
        Route::prefix('manage-tim')->group(function(){
            Route::get('/', [ManageTimController::class, 'index']);
        });

        Route::prefix('approval-leave')->group(function(){
            Route::get('/', [ApprovalLeave::class, 'index']);
            Route::get('/{id}', [ApprovalLeave::class, 'show']);
            Route::put('/{id}', [ApprovalLeave::class, 'update']);
        });

        Route::prefix('approval-permit')->group(function(){
            Route::get('/', [ApprovalPermit::class, 'index']);
            Route::get('/{id}', [ApprovalPermit::class, 'show']);
            Route::put('/{id}', [ApprovalPermit::class, 'update']);
        });

        Route::prefix('approval-overtime')->group(function(){
            Route::get('/', [ApprovalOvertime::class, 'index']);
            Route::get('/{id}', [ApprovalOvertime::class, 'show']);
            Route::put('/{id}', [ApprovalOvertime::class, 'update']);
        });

        Route::prefix('approval-minute')->group(function(){
            Route::get('/', [ApprovalMinute::class, 'index']);
            Route::get('/{id}', [ApprovalMinute::class, 'show']);
            Route::put('/{id}', [ApprovalMinute::class, 'update']);
        });

        Route::prefix('switch-schedule')->group(function(){
            Route::get('/', [SwitchSheduleController::class, 'index']);
            Route::get('/{id}', [SwitchSheduleController::class, 'switchSchedule']);
            Route::get('/{id}/userSchedule', [SwitchSheduleController::class, 'userSchedule']);
            Route::post('/swapSchedules', [SwitchSheduleController::class, 'swapSchedules']);
        });

        Route::prefix('report-patroll')->group(function(){
            Route::get('/', [ReportPatroll::class, 'index']);
        });

    });

    // scan floor
    Route::post('scan_floor', [SecurityPatrollController::class, 'scan_floor']);

    Route::get('/ping', function () {
        return response()->json([
            'status' => 'ok',
            'message' => 'Server is alive',
            'timestamp' => now()
        ]);
    });

});
