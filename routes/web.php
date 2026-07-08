<?php

use App\Http\Controllers\Applicant\SiteController as ApplicantSiteController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomVariableController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeNikConfigController;
use App\Http\Controllers\SalarySettingController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LetterNumberConfigController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KtpOcrController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TypeLetterController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('manage')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->middleware('web')->name('logout');
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

Route::get('/career', [DashboardController::class, 'career'])->name('web-career');
Route::get('/career/{id}/detail', [DashboardController::class, 'careerDetail'])->name('web-career-detail');

Route::middleware(['auth'])->prefix('account')->group(function () {
    Route::get('/tab-account-detail', [DashboardController::class, 'indexAccount'])->name('web-account');
    Route::get('/tab-profile-detail', [DashboardController::class, 'indexProfile'])->name('web-profile');
    Route::get('/tab-document-detail', [DashboardController::class, 'indexDocument'])->name('web-document');
});

Route::middleware('auth')->group(function () {
    Route::post('/import/process', [ImportController::class, 'processImport'])->name('import.process');
    Route::post('/admins/import', [AdminController::class, 'import'])->name('admins.import');
    Route::post('/admins/export', [AdminController::class, 'export'])->name('admins.export');
    Route::get('/sites/export', [SiteController::class, 'export'])->name('sites.export');
    Route::post('/sites/import', [SiteController::class, 'import'])->name('sites.import');
    Route::get('/employee/export', [EmployeeController::class, 'export'])->name('employee.export');
});

Route::middleware(['auth'])->prefix('manage')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/whatsapp-config', [DashboardController::class, 'whatsappConfig'])->name('whatsapp.config');
    Route::get('/whatsapp-status', [DashboardController::class, 'getWhatsappStatus'])->name('whatsapp.status');
    Route::post('/whatsapp-disconnect', [DashboardController::class, 'disconnectWhatsapp'])->name('whatsapp.disconnect');
    Route::get('/comingsoon', [DashboardController::class, 'comingsoon'])->name('comingsoon');
    Route::get('/recuit', [DashboardController::class, 'recruit'])->name('recruit');
    Route::get('/activities', [DashboardController::class, 'activities'])->name('activities');

    Route::resource('roles', RoleController::class);
    Route::resource('admins', AdminController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('sites', SiteController::class);

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/company/{company}', [EmployeeController::class, 'byCompany'])->name('employees.company');
    Route::post('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');

    Route::get('/get-sites-by-company/{company_id}', [StatusController::class, 'getSitesByCompany'])->name('sites.by.company');
    Route::get('get-custom-variables/{letterId}', [StatusController::class, 'getCustomVariables'])->name('get.custom.variables');
    Route::get('/get-letters-by-site/{site_id}', [StatusController::class, 'getLettersBySite'])->name('letters.by.site');
    Route::resource('statuses', StatusController::class);

    Route::post('/generates/export-template', [GenerateController::class, 'exportTemplate'])->name('generates.export-template');
    Route::post('/generates/import-template', [GenerateController::class, 'importTemplate'])->name('generates.import-template');
        Route::get('/generate-folders', [GenerateController::class, 'folders'])->name('generates.folders');
        Route::resource('generates', GenerateController::class);
    Route::get('/generates/{generate}/pdf', [GenerateController::class, 'pdf'])->name('generates.pdf');
    Route::get('/generates/{generate}/print', [GenerateController::class, 'printView'])->name('generates.print');
    Route::post('/bulk-approve', [GenerateController::class, 'bulkApprove'])->name('generates.bulkApprove');
    Route::post('/bulk-delete', [GenerateController::class, 'bulkDelete'])->name('generates.bulkDelete');
    Route::get('/letter/{id}/regenerate', [GenerateController::class, 'regenerate'])->name('letter-regenerate');

    Route::resource('type_letters', TypeLetterController::class);

    Route::post('/letters/import', [LetterController::class, 'import'])->name('letters.import');
    Route::resource('letters', LetterController::class);
    Route::post('/letters/{letter}/duplicate', [LetterController::class, 'duplicate'])->name('letters.duplicate');
    Route::get('/letters/{letter}/pdf', [LetterController::class, 'pdf'])->name('letters.pdf');
    Route::get('/letters/{letter}/print', [LetterController::class, 'printView'])->name('letters.print');
    Route::get('/letters/{letter}/number-preview', [LetterController::class, 'numberPreview'])->name('letters.number-preview');
    Route::post('/letters/upload-image', [LetterController::class, 'uploadImage'])->name('letters.upload-image');
    Route::resource('letter-number-configs', LetterNumberConfigController::class);
    Route::post('/employee-nik-configs/preview', [EmployeeNikConfigController::class, 'preview'])->name('employee-nik-configs.preview');
    Route::resource('employee-nik-configs', EmployeeNikConfigController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::post('/salary-settings/export', [SalarySettingController::class, 'export'])->name('salary-settings.export');
    Route::post('/salary-settings/import', [SalarySettingController::class, 'import'])->name('salary-settings.import');
    Route::resource('salary-settings', SalarySettingController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('custom-variables', CustomVariableController::class);

    Route::resource('careers', CareerController::class);
    Route::put('/careers/{id}/update-status', [CareerController::class, 'updateStatus'])->name('update-career');
    Route::get('/careers/{id}/banner', [CareerController::class, 'banner'])->name('banner-career');

    Route::get('/applicants', [ApplicantController::class, 'index'])->name('applicants.index');
    Route::get('/applicants/create', [ApplicantController::class, 'create'])->name('applicants.create');
    Route::post('/applicants', [ApplicantController::class, 'store'])->name('applicants.store');
    Route::get('/applicants/{id}', [ApplicantController::class, 'show'])->name('applicants.show');
    Route::put('/applicants/{id}/update-status', [ApplicantController::class, 'updateStatus'])->name('update-status');
    Route::put('/applicants/{id}/update-approve', [ApplicantController::class, 'updateApprove'])->name('update-approve');
    Route::post('/statuses/bulk-update', [StatusController::class, 'bulkUpdateStatus'])->name('bulk.update.status');
    Route::post('/statuses/bulk-applicant-document', [StatusController::class, 'bulkUpdateApplicantDocument'])->name('bulk.update.applicant-document');
    Route::post('/statuses/bulk-offering', [StatusController::class, 'bulkUpdateOffering'])->name('bulk.update.offering');
    Route::delete('/applicants/{id}', [ApplicantController::class, 'destroy'])->name('applicants.destroy');
    Route::get('/applicants/{id}/resume', [ApplicantController::class, 'resume'])->name('applicants.resume');
    Route::put('/applicants/{id}/status', [ApplicantController::class, 'updateStatusSingle'])->name('applicants.update-status-single');
    Route::post('/applicants/{id}/set-employee', [ApplicantController::class, 'setEmployee'])->name('applicants.set-employee');
    Route::post('/applicants/reset-all-qr', [ApplicantController::class, 'resetAllQr'])->name('applicants.reset-all-qr');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profiles.index');
    Route::put('/profile', [ProfileController::class, 'updateAccount'])->name('profiles.update.account');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profiles.update.profile');
    Route::post('/profile/document/create', [ProfileController::class, 'storeDocument'])->name('profiles.document.store');

    Route::post('/users/personal-data/{id}', [AdminController::class, 'updatePersonalData'])->name('personal-data-user');
    Route::post('/users/site-zone/{id}', [AdminController::class, 'updateSiteZone'])->name('site-zone-user');
    Route::get('/profile/{id}/resume', [AdminController::class, 'indexResume'])->name('users.resume');
    Route::get('/profile/{id}/detail', [AdminController::class, 'indexAccount'])->name('users.account');
    Route::put('/profile/{id}/detail', [AdminController::class, 'updateAccount'])->name('users.update.account');
    Route::post('/profile/{id}/profile/update', [AdminController::class, 'updateProfile'])->name('users.update.profile');
    Route::post('/profile/{id}/document/create', [AdminController::class, 'storeDocument'])->name('users.document.store');
    Route::post('/profile/{id}/mutation/create', [AdminController::class, 'storeMutation'])->name('users.mutation.store');

    Route::post('/save-signature', [SignatureController::class, 'store'])->name('save.signature');
    Route::delete('/delete-signature', [SignatureController::class, 'delete'])->name('delete.signature');

    Route::post('/ktp-ocr/openai', [KtpOcrController::class, 'openai'])->name('ktp-ocr.openai');
});

require __DIR__ . '/guest.php';
