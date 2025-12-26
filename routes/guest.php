<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Applicant\SiteController;
use App\Http\Controllers\Applicant\ApplicantAuthController;

Route::middleware(['applicant.auth'])->group(function () {   
    Route::get('/', [SiteController::class, 'index']);
});


Route::prefix('applicant')->group(function () {
    Route::get('/login', [ApplicantAuthController::class, 'showLoginForm'])->name('applicant-login');
    Route::post('/login', [ApplicantAuthController::class, 'storeLogin'])->name('applicant-login-store');
    Route::post('/logout', [ApplicantAuthController::class, 'logout'])->middleware('web')->name('applicant-logout');
    Route::get('/register', [ApplicantAuthController::class, 'showRegisterForm'])->name('applicant-register');
    Route::post('/register', [ApplicantAuthController::class, 'storeRegister'])->name('applicant-register-store');

});

Route::middleware(['applicant.auth'])->prefix('applicant')->group(function () {   
    Route::get('/dashboard', [SiteController::class, 'dashboard'])->name('web.applicants.dashboard');
    Route::get('/lowongan-pekerjaan', [SiteController::class, 'index'])->name('web.applicants.career');
    Route::get('/lowongan-pekerjaan/{slug}', [SiteController::class, 'detail'])->name('web.applicants.career.detail');
    Route::post('/lowongan-pekerjaan/{slug}/apply', [SiteController::class, 'apply'])->name('web.applicants.career.apply');

    Route::get('/my-profile', [SiteController::class, 'indexProfile'])->name('applicants.profiles.index');
    Route::put('/applicant/profile', [SiteController::class, 'updateAccount'])->name('applicants.profiles.update.account');
    Route::post('/applicant/profile/update',[SiteController::class, 'updateProfile'])->name('applicants.profiles.update.profile');
    Route::post('/applicant/profile/document/create',[SiteController::class, 'storeDocument'])->name('applicants.profiles.document.store');
});
    