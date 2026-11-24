<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Applicant\DataController;
use App\Http\Controllers\Applicant\ApplicantAuthController;
use App\Http\Controllers\Applicant\OnboardingController;

Route::middleware(['applicant.auth', 'applicant.onboarded'])->group(function () {
    Route::get('/', [DataController::class, 'index']);
});

// File dokumen milik sendiri (pratinjau di modal, tanpa link storage). Tanpa applicant.onboarded
// agar bisa dilihat dari wizard onboarding.
Route::middleware(['applicant.auth'])->get('/applicant/dokumen/{document}/file', [\App\Http\Controllers\DocumentFileController::class, 'own'])
    ->name('applicant.documents.file');

// Wizard "Lengkapi Profil" setelah daftar (tanpa applicant.onboarded agar tidak redirect berulang).
Route::middleware(['applicant.auth'])->prefix('applicant/lengkapi-profil')->group(function () {
    Route::get('/{step?}', [OnboardingController::class, 'show'])->whereNumber('step')->name('applicant.onboarding');
    Route::post('/dokumen', [OnboardingController::class, 'uploadDocument'])->name('applicant.onboarding.document');
    Route::post('/{step}', [OnboardingController::class, 'save'])->whereNumber('step')->name('applicant.onboarding.save');
    // Scan KTP dari wizard (sekaligus menyimpan foto KTP ke dokumen)
    Route::post('/scan-ktp', [\App\Http\Controllers\KtpOcrController::class, 'scan'])->name('applicant.onboarding.ocr-scan');
});


Route::prefix('applicant')->group(function () {
    Route::get('/login', [ApplicantAuthController::class, 'showLoginForm'])->name('applicant-login');
    Route::post('/login', [ApplicantAuthController::class, 'storeLogin'])->middleware('throttle:10,1')->name('applicant-login-store');
    Route::post('/logout', [ApplicantAuthController::class, 'logout'])->middleware('web')->name('applicant-logout');
    Route::get('/register', [ApplicantAuthController::class, 'showRegisterForm'])->name('applicant-register');
    Route::post('/register', [ApplicantAuthController::class, 'storeRegister'])->name('applicant-register-store');

    Route::get('/forgot-password', [ApplicantAuthController::class, 'showForgotForm'])->name('applicant-forgot');
    Route::post('/forgot-password', [ApplicantAuthController::class, 'sendOtp'])->middleware('throttle:5,1')->name('applicant-forgot-send');
    Route::get('/verify-otp', [ApplicantAuthController::class, 'showOtpForm'])->name('applicant-otp');
    Route::post('/verify-otp', [ApplicantAuthController::class, 'verifyOtp'])->middleware('throttle:5,1')->name('applicant-otp-verify');
    Route::get('/reset-password', [ApplicantAuthController::class, 'showResetForm'])->name('applicant-reset');
    Route::post('/reset-password', [ApplicantAuthController::class, 'resetPassword'])->name('applicant-reset-store');
});

Route::middleware(['applicant.auth', 'applicant.onboarded'])->prefix('applicant')->group(function () {
    Route::get('/dashboard', [DataController::class, 'dashboard'])->name('web.applicants.dashboard');
    Route::get('/pertanyaan', [DataController::class, 'faq'])->name('web.applicants.faq');
    Route::get('/riwayat-lamar-pekerjaan', [DataController::class, 'history'])->name('web.applicants.history');
    Route::get('/berkas', [DataController::class, 'letter'])->name('web.applicants.letter');
    Route::get('/{id}/tanda-tangan-digital', [DataController::class, 'letterDetail'])->name('web.applicants.letter.detail');
    Route::get('/{id}/surat-pdf', [DataController::class, 'letterPdf'])->name('web.applicants.letter.pdf');
    Route::get('/{id}/surat-print', [DataController::class, 'letterPrint'])->name('web.applicants.letter.print');
    Route::post('/tanda-tangan-digital/sign/{id}', [DataController::class, 'sign'])->name('web.applicants.letter.sign');
    Route::get('/lowongan-pekerjaan', [DataController::class, 'index'])->name('web.applicants.career');
    Route::get('/lowongan-pekerjaan/{slug}', [DataController::class, 'detail'])->name('web.applicants.career.detail');
    Route::post('/lowongan-pekerjaan/{slug}/apply', [DataController::class, 'apply'])->name('web.applicants.career.apply');

    Route::get('/my-profile', [DataController::class, 'indexProfile'])->name('applicants.profiles.index');
    Route::get('/profile/ocr', [DataController::class, 'ocrPage'])->name('applicant.profiles.ocr');
    Route::post('/profile/ocr/update', [DataController::class, 'storeOcr'])->name('applicant.profiles.update-ocr');
    Route::post('/profile/ocr/scan', [\App\Http\Controllers\KtpOcrController::class, 'scan'])->name('applicant.profiles.ocr-scan');
    Route::put('/applicant/profile', [DataController::class, 'updateAccount'])->name('applicants.profiles.update.account');
    Route::post('/applicant/profile/update',[DataController::class, 'updateProfile'])->name('applicants.profiles.update.profile');
    Route::post('/applicant/profile/document/create',[DataController::class, 'storeDocument'])->name('applicants.profiles.document.store');
});
    