<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ReportDaily;
use Cloudinary\Transformation\Rotate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SecurtyPatroll;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ValetController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\MinuteController;
use App\Http\Controllers\PermitController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobdeskController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\TypeLeaveController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TypeLetterController;
use App\Http\Controllers\TaskPlannerController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ComponentTypeController;
use App\Http\Controllers\DeductionTypeController;
use App\Http\Controllers\FindingReportController;
use App\Http\Controllers\ReportPatrollController;
use App\Http\Controllers\FaceAttendanceController;
use App\Http\Controllers\JobdeskPatrollsController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\PayrollComponentController;
use App\Http\Controllers\TimeDeductionTypeController;
use App\Http\Controllers\Applicant\SiteController as ApplicantSiteController;

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

Route::get('/face/account/register', [FaceAttendanceController::class, 'showFaceRegisterForm'])->name('face.account.register');
Route::post('/face/account/store', [FaceAttendanceController::class, 'storeAccountAndFace'])->name('face.account.store');

Route::get('/attendance-form', [FaceAttendanceController::class, 'showAttendanceForm'])->name('face.attendance.form');
Route::post('/face-attendance', [FaceAttendanceController::class, 'processFaceAttendance'])->name('face.attendance.process');

Route::middleware('auth')->group(function () {


    Route::post('/import/process', [ImportController::class, 'processImport'])->name('import.process');
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
    Route::post('/users/export', [UserController::class, 'export'])->name('users.export');
    Route::get('/sites/export', [SiteController::class, 'export'])->name('sites.export');
    Route::post('/sites/import', [SiteController::class, 'import'])->name('sites.import');
    Route::get('/employee/export', [ReportController::class, 'employeeExport'])->name('employee.export');
    Route::get('/export/excel', [ReportController::class, 'exportToExcel'])->name('export.excel');
    Route::get('/payroll/payslip/download/{id}', [PayrollController::class, 'downloadPayslip'])->name('payroll.downloadPayslip');
    Route::post('/leaves/export', [LeaveController::class, 'export'])->name('leaves.export');
    Route::post('/permits/export', [PermitController::class, 'export'])->name('permits.export');
    Route::post('/overtimes/export', [OvertimeController::class, 'export'])->name('overtimes.export');
});

Route::middleware(['auth', 'check.desktop'])->prefix('manage')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::get('/dashboard', [DashboardController::class, 'recruit'])->name('dashboard');
    Route::get('/comingsoon', [DashboardController::class, 'comingsoon'])->name('comingsoon');
    Route::get('/recuit', [DashboardController::class, 'recruit'])->name('recruit');
    Route::get('/activities', [DashboardController::class, 'activities'])->name('activities');
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('employees', UserController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('sites', SiteController::class);

    Route::resource('statuses', StatusController::class);
    Route::resource('valets', ValetController::class);

    Route::resource('generates', GenerateController::class);
    Route::post('/bulk-approve', [GenerateController::class, 'bulkApprove'])->name('generates.bulkApprove');
    Route::post('/bulk-delete', [GenerateController::class, 'bulkDelete'])->name('generates.bulkDelete');

    Route::resource('attendances', AttendanceController::class);
    Route::get('/attendances/filter', [AttendanceController::class, 'filter'])->name('attendances.filter');
    Route::resource('overtimes', OvertimeController::class);
    Route::resource('minutes', MinuteController::class);

    Route::resource('leaves', LeaveController::class);

    Route::resource('permits', PermitController::class);
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/{id}/show', [ScheduleController::class, 'show'])->name('schedules.show');
    Route::post('/schedules/import', [ScheduleController::class, 'import'])->name('schedules.import');
    Route::post('/schedules/shift/store', [ScheduleController::class, 'shiftStore'])->name('schedules.shift.store');

    // task palnner
    Route::get('/task-planner', [TaskPlannerController::class, 'index'])->name('tasks.index');
    Route::get('/task-planner/{id}/show', [TaskPlannerController::class, 'show'])->name('tasks.show');
    Route::post('/task-planners/store', [TaskPlannerController::class, 'store']);
    Route::post('/task-planners/store/jobdesk-to-task', [TaskPlannerController::class, 'jobToTaskPlan'])->name('jobdesk-to-task.store');
    Route::get('/task-planners/events/{site_id}', [TaskPlannerController::class, 'getEvents']);
    Route::get('/task-planner/{id}/edit', [TaskPlannerController::class, 'edit']);
    Route::post('/task-planners/update', [TaskPlannerController::class, 'update'])->name('jobdesk-to-task.update');
    Route::post('/task-planner/jobdesk/store', [JobdeskController::class, 'store'])->name('tasks.jobdesk.store');
    Route::delete('/task-planner/{id}', [TaskPlannerController::class, 'destroy'])->name('tasks.delete');
    Route::post('/task-planner/import', [TaskPlannerController::class, 'import'])->name('tasks.import');

    // jobdesk
    Route::get('/jobdesk-patrolls', [JobdeskPatrollsController::class, 'index'])->name('jobdesk-patrolls.index');
    Route::get('/jobdesk-patrolls/{id}', [JobdeskPatrollsController::class, 'show'])->name('jobdesk-patrolls.show');
    Route::post('/jobdesk-patrolls/store', [JobdeskPatrollsController::class, 'addJob'])->name('jobdesk-patrolls.addJob');
    Route::put('/jobdesk-patrolls/{id}/update-jobdesk', [JobdeskPatrollsController::class, 'update'])->name('jobdesk-patrolls.update');
    Route::delete('/jobdesk-patrolls/{id}', [JobdeskPatrollsController::class, 'delete'])->name('jobdesk-patrolls.delete');

    Route::post('/clean-duplicate-leaves', [LeaveController::class, 'cleanDuplicateLeaves'])->name('cleanDuplicateLeaves');
    Route::resource('type_letters', TypeLetterController::class);
    Route::resource('types', TypeLeaveController::class);
    Route::get('/letter/{id}/regenerate', [GenerateController::class, 'regenerate'])->name('letter-regenerate');

    Route::resource('careers', CareerController::class);
    Route::put('/careers/{id}/update-status', [CareerController::class, 'updateStatus'])->name('update-career');
    Route::get('/careers/{id}/banner', [CareerController::class, 'banner'])->name('banner-career');

    Route::resource('letters', LetterController::class);

    Route::resource('taxrates', TaxRateController::class);
    Route::resource('payrollcomponents', PayrollComponentController::class);
    Route::get('/payrolls/main', [PayrollController::class, 'main'])->name('payrolls.main');
    Route::get('/payrolls/{id}/detail', [PayrollController::class, 'detailPayroll'])->name('payrolls.detail');
    Route::put('/payrolls/{id}', [PayrollController::class, 'update'])->name('payrolls.update');
    Route::post('/payrolls/site/update', [PayrollController::class, 'updatePayroll'])->name('payrolls.site.update');
    Route::post('/payrolls/bulk-update', [PayrollController::class, 'bulkUpdate'])->name('payrolls.bulk-update');
    Route::post('/payrolls/allowance/add', [ComponentTypeController::class, 'store'])->name('payrolls.allowance');
    Route::post('/payrolls/deduction/add', [DeductionTypeController::class, 'store'])->name('payrolls.deduction');
    // Route::post('/payrolls/time-deduction/add', [TimeDeductionTypeController::class, 'store'])->name('payrolls.time-deduction');

    Route::get('/payrolls/overtime/request', [OvertimeRequestController::class, 'index'])->name('payrolls.overtime');
    Route::patch('/overtimes/{overtime}/status', [OvertimeRequestController::class, 'updateStatus'])->name('overtimes.updateStatus');

    Route::get('/payrolls/generate', [PayrollController::class, 'generateIndex'])->name('payrolls.generate');
    Route::post('/payrolls/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
    Route::get('/payrolls/generate/{id}/{period}', [PayrollController::class, 'generateDetail'])->name('payroll.generateDetail');
    Route::delete('/payrolls/delete/{site_id}/{period}', [PayrollController::class, 'destroy'])->name('payroll.generate.destroy');
    Route::get('/payroll/generate-payslip/{id}/{period}', [PayrollController::class, 'generatePayslip'])->name('payroll.generatePayslip');
    Route::get('/payroll/payslip/{id}', [PayrollController::class, 'viewPayslip'])->name('payroll.viewPayslip');

    Route::get('/applicants', [ApplicantController::class, 'index'])->name('applicants.index');
    Route::get('/applicants/create', [ApplicantController::class, 'create'])->name('applicants.create');
    Route::post('/applicants', [ApplicantController::class, 'store'])->name('applicants.store');
    Route::get('/applicants/{id}', [ApplicantController::class, 'show'])->name('applicants.show');
    Route::put('/applicants/{id}/update-status', [ApplicantController::class, 'updateStatus'])->name('update-status');
    Route::put('/applicants/{id}/update-approve', [ApplicantController::class, 'updateApprove'])->name('update-approve');
    Route::post('/statuses/bulk-update', [StatusController::class, 'bulkUpdateStatus'])->name('bulk.update.status');
    Route::post('/statuses/bulk-offering', [StatusController::class, 'bulkUpdateOffering'])->name('bulk.update.offering');
    Route::delete('/applicants/{id}', [ApplicantController::class, 'destroy'])->name('applicants.destroy');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profiles.index');
    Route::put('/profile', [ProfileController::class, 'updateAccount'])->name('profiles.update.account');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profiles.update.profile');
    Route::post('/profile/document/create', [ProfileController::class, 'storeDocument'])->name('profiles.document.store');

    Route::post('/users/personal-data/{id}', [UserController::class, 'updatePersonalData'])->name('personal-data-user');
    Route::post('/users/site-zone/{id}', [UserController::class, 'updateSiteZone'])->name('site-zone-user');
    Route::get('/profile/{id}/resume', [UserController::class, 'indexResume'])->name('users.resume');
    Route::get('/profile/{id}/detail', [UserController::class, 'indexAccount'])->name('users.account');
    Route::put('/profile/{id}/detail', [UserController::class, 'updateAccount'])->name('users.update.account');
    Route::post('/profile/{id}/profile/update', [UserController::class, 'updateProfile'])->name('users.update.profile');
    Route::post('/profile/{id}/document/create', [UserController::class, 'storeDocument'])->name('users.document.store');
    Route::post('/profile/{id}/mutation/create', [UserController::class, 'storeMutation'])->name('users.mutation.store');

    Route::post('/save-signature', [SignatureController::class, 'store'])->name('save.signature');
    Route::delete('/delete-signature', [SignatureController::class, 'delete'])->name('delete.signature');

    Route::get('/report/attendance', [ReportController::class, 'attendanceReport'])->name('attendance.report');

    Route::get('/report/employee/view', [ReportController::class, 'employeeView'])->name('report.employee.view');
    Route::get('/report/site/export', [ReportController::class, 'siteExport'])->name('report.site.export');
    Route::get('/report/site/view', [ReportController::class, 'siteView'])->name('report.site.view');
    Route::get('/export/active-users', [ReportController::class, 'exportActiveUser'])->name('export.active-users');
    Route::get('/export/inactive-users', [ReportController::class, 'exportInactiveUser'])->name('export.inactive-users');

    // report temuan
    Route::get('/finding-Report', [FindingReportController::class, 'index'])->name('findingReport.index');
    Route::put('/finding-Report/{id}', [FindingReportController::class, 'update'])->name('findingReport.update');
    Route::delete('/finding-Report/{id}', [FindingReportController::class, 'destroy'])->name('findingReport.destroy');
    Route::get('/finding-Report/export', [FindingReportController::class, 'export'])->name('findingReport.export');

    // daily report
    Route::get('/daily-report', [ReportDaily::class, 'index'])->name('dailyReport.index');
    Route::put('/daily-report/{id}', [ReportDaily::class, 'update'])->name('dailyReport.update');
    Route::delete('/daily-report/{id}', [ReportDaily::class, 'destroy'])->name('dailyReport.destroy');
    Route::get('/daily-report/export', [ReportDaily::class, 'export'])->name('dailyReport.export');

    // floor
    Route::get('/floors', [FloorController::class, 'index'])->name('floors.index');
    Route::post('floors', [FloorController::class, 'addFloor'])->name('floors.store');
    Route::put('/floors/{id}', [FloorController::class, 'update'])->name('floors.update');
    Route::delete('/floors/{id}', [FloorController::class, 'destroy'])->name('floors.destroy');
    Route::get('/floors/export', [FloorController::class, 'export'])->name('floors.export');
    Route::post('/floors/import', [FloorController::class, 'import'])->name('floors.import');

    // securty-patroll
    Route::get('/securty-patroll', [SecurtyPatroll::class, 'index'])->name('securty-patroll.index');
    Route::get('/securty-patroll/{id}/showFloor', [SecurtyPatroll::class, 'showFloor'])->name('securty-patroll.showFloor');
    Route::get('/securty-patroll/{id}/showTask', [SecurtyPatroll::class, 'showTask'])->name('securty-patroll.showTask');
    Route::get('/securty-patroll/{id}/print', [SecurtyPatroll::class, 'exportAll'])->name('securty-patroll.print');

    // report patroll
    Route::get('/patroll-report', [ReportPatrollController::class, 'index'])->name('patrollReport.index');
    Route::get('/patroll-report/export', [ReportPatrollController::class, 'export'])->name('patrollReport.export');
    Route::get('/patroll-report/{id}/print', [ReportPatrollController::class, 'printTodayReport'])->name('patrollReport.print');
    Route::put('/patroll-report/{id}', [ReportPatrollController::class, 'update'])->name('patrollReport.update');
    Route::delete('/patroll-report/{id}', [ReportPatrollController::class, 'destroy'])->name('patrollReport.destroy');
});

require __DIR__ . '/guest.php';
