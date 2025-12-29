<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Applicant\DataController;
use App\Http\Controllers\Applicant\ApplicantAuthController;

Route::middleware(['applicant.auth'])->group(function () {   
    Route::get('/', [DataController::class, 'index']);
});


Route::prefix('applicant')->group(function () {
    Route::get('/login', [ApplicantAuthController::class, 'showLoginForm'])->name('applicant-login');
    Route::post('/login', [ApplicantAuthController::class, 'storeLogin'])->name('applicant-login-store');
    Route::post('/logout', [ApplicantAuthController::class, 'logout'])->middleware('web')->name('applicant-logout');
    Route::get('/register', [ApplicantAuthController::class, 'showRegisterForm'])->name('applicant-register');
    Route::post('/register', [ApplicantAuthController::class, 'storeRegister'])->name('applicant-register-store');

});

Route::middleware(['applicant.auth'])->prefix('applicant')->group(function () {   
    Route::get('/dashboard', [DataController::class, 'dashboard'])->name('web.applicants.dashboard');
    Route::get('/lowongan-pekerjaan', [DataController::class, 'index'])->name('web.applicants.career');
    Route::get('/lowongan-pekerjaan/{slug}', [DataController::class, 'detail'])->name('web.applicants.career.detail');
    Route::post('/lowongan-pekerjaan/{slug}/apply', [DataController::class, 'apply'])->name('web.applicants.career.apply');

    Route::get('/my-profile', [DataController::class, 'indexProfile'])->name('applicants.profiles.index');
    Route::put('/applicant/profile', [DataController::class, 'updateAccount'])->name('applicants.profiles.update.account');
    Route::post('/applicant/profile/update',[DataController::class, 'updateProfile'])->name('applicants.profiles.update.profile');
    Route::post('/applicant/profile/document/create',[DataController::class, 'storeDocument'])->name('applicants.profiles.document.store');
});
    