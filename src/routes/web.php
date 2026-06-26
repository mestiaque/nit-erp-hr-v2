<?php

use Illuminate\Support\Facades\Route;
use ME\Hr\Http\Controllers\HrController;
use ME\Hr\Http\Controllers\HrDashboardController;
use ME\Hr\Http\Controllers\HrEmployeeController;
use ME\Hr\Http\Controllers\HrHolidayController;
use ME\Hr\Http\Controllers\HrFloorLineController;
use ME\Hr\Http\Controllers\HrMasterController;
use ME\Hr\Http\Controllers\HrReportController;
use ME\Hr\Http\Controllers\ProductionRateController;
use ME\Hr\Http\Controllers\RosterController;
use ME\Hr\Http\Controllers\RegularToWeekendController;
use ME\Hr\Http\Controllers\AttendanceMachineController;

$route = config('hr.route');

Route::middleware(['web'])->get('/hr', [HrController::class, 'index']);
Route::get('/thanas/by-district/{id}', [HrController::class, 'getThanasByDistrict']);

Route::middleware($route['middleware'] ?? ['web'])
    ->prefix($route['prefix'] ?? 'admin/hr-center')
    ->name($route['as'] ?? 'hr-center.')
    ->group(function () {
		// Attendance Management
		Route::get('/attendances', [\ME\Hr\Http\Controllers\AttendanceController::class, 'index'])->name('attendances.index');
		Route::get('/attendances/{user}/{date}/edit', [\ME\Hr\Http\Controllers\AttendanceController::class, 'edit'])->name('attendances.edit');
		Route::post('/attendances/{user}/{date}', [\ME\Hr\Http\Controllers\AttendanceController::class, 'update'])->name('attendances.update');
		Route::get('/', [HrDashboardController::class, 'index'])->name('dashboard');
		Route::get('/employees', [HrEmployeeController::class, 'index'])->name('employees.index');
		Route::post('/employees', [HrEmployeeController::class, 'store'])->name('employees.store');
		Route::put('/employees/{employee}/profile', [HrEmployeeController::class, 'updateProfile'])->name('employees.profile.update');
		Route::put('/employees/{employee}/salary', [HrEmployeeController::class, 'updateSalary'])->name('employees.salary.update');
		Route::put('/employees/{employee}/address', [HrEmployeeController::class, 'updateAddress'])->name('employees.address.update');
		Route::put('/employees/{employee}/nominee', [HrEmployeeController::class, 'updateNominee'])->name('employees.nominee.update');
		Route::put('/employees/{employee}/age-verification', [HrEmployeeController::class, 'updateAgeVerification'])->name('employees.age.update');
		Route::put('/employees/{employee}/resign', [HrEmployeeController::class, 'updateResign'])->name('employees.resign.update');
		Route::put('/employees/{employee}/final-settlement', [HrEmployeeController::class, 'updateFinalSettlement'])->name('employees.final-settlement.update');
		Route::put('/employees/{employee}/final-settlement/print', [HrEmployeeController::class, 'updateFinalSettlement'])->name('employees.final-settlement.print');

		Route::put('/employees/{employee}/basic-info', [HrEmployeeController::class, 'updateBasicInfo'])->name('employees.basic-info.update');
		Route::get('/employees/{employee}/increments', [HrEmployeeController::class, 'incrementsPage'])->name('employees.increments.page');
		Route::post('/employees/{employee}/increments', [HrEmployeeController::class, 'incrementsStore'])->name('employees.increments.store');
		Route::put('/employees/{employee}/increments', [HrEmployeeController::class, 'incrementsUpdate'])->name('employees.increments.update');
		Route::get('/employees/{employee}/earnings-deductions', [HrEmployeeController::class, 'earningsDeductionsPage'])->name('employees.earnings.page');
		Route::post('/employees/{employee}/earnings-deductions', [HrEmployeeController::class, 'earningsDeductionsStore'])->name('employees.earnings.store');
		Route::put('/employees/{employee}/earnings-deductions', [HrEmployeeController::class, 'earningsDeductionsUpdate'])->name('employees.earnings.update');
		Route::delete('/employees/{employee}/earnings-deductions', [HrEmployeeController::class, 'earningsDeductionsDelete'])->name('employees.earnings.delete');
		Route::get('/employees/{employee}/leaves', [HrEmployeeController::class, 'leavesPage'])->name('employees.leaves.page');
		Route::post('/employees/{employee}/leaves', [HrEmployeeController::class, 'leavesStore'])->name('employees.leaves.store');
		Route::put('/employees/{employee}/leaves', [HrEmployeeController::class, 'leavesUpdate'])->name('employees.leaves.update');
		Route::delete('/employees/{employee}/leaves', [HrEmployeeController::class, 'leavesDelete'])->name('employees.leaves.delete');
		Route::get('/employees/{employee}/documents', [HrEmployeeController::class, 'documentsPage'])->name('employees.documents.page');
		Route::post('/employees/{employee}/documents', [HrEmployeeController::class, 'documentsStore'])->name('employees.documents.store');
		Route::delete('/employees/{employee}/documents', [HrEmployeeController::class, 'documentsDelete'])->name('employees.documents.delete');
		Route::delete('/employees/{employee}', [HrEmployeeController::class, 'destroy'])->name('employees.destroy');
		// Route::get('/employees/{employee}/print/{section}', [HrEmployeeController::class, 'printSection'])->name('employees.print.section');
		Route::get('/reports/pro-job-card', [HrReportController::class, 'proJobCard'])->name('reports.pro-job-card');
		// Individual Pay Slip Report (like Job Card)
		Route::get('/reports/individual-pay-slip', [HrReportController::class, 'individualPaySlipReport'])->name('reports.individual-pay-slip');
		Route::get('/reports/attendance-with-ot', [HrReportController::class, 'attendanceWithOt'])->name('reports.attendance-with-ot');
		Route::get('/reports/monthly-late-report', [HrReportController::class, 'monthlyLateReport'])->name('reports.monthly-late-report');
		Route::get('/reports', [HrReportController::class, 'index'])->name('reports.index');
		Route::get('/reports/{report}', [HrReportController::class, 'show'])->name('reports.show');
		Route::post('/reports/monthly/lock-increment', [HrReportController::class, 'lockMonthlyIncrement'])->name('reports.monthly.lock-increment');
		Route::post('/reports/job-card-report/lock', [HrReportController::class, 'applyJobCardLock'])->name('reports.job-card-report.lock');

		Route::get('/holidays', [HrHolidayController::class, 'index'])->name('holidays.index');
		Route::post('/holidays', [HrHolidayController::class, 'store'])->name('holidays.store');
		Route::put('/holidays/{id}', [HrHolidayController::class, 'update'])->name('holidays.update');
		Route::delete('/holidays/{id}', [HrHolidayController::class, 'destroy'])->name('holidays.destroy');

		// Floor Lines (Block / Line)
		Route::get('/masters/floor-lines', [HrFloorLineController::class, 'index'])->name('floor-lines.index');
		Route::get('/masters/floor-lines/create', [HrFloorLineController::class, 'create'])->name('floor-lines.create');
		Route::post('/masters/floor-lines', [HrFloorLineController::class, 'store'])->name('floor-lines.store');
		Route::get('/masters/floor-lines/{id}/edit', [HrFloorLineController::class, 'edit'])->name('floor-lines.edit');
		Route::put('/masters/floor-lines/{id}', [HrFloorLineController::class, 'update'])->name('floor-lines.update');
		Route::delete('/masters/floor-lines/{id}', [HrFloorLineController::class, 'destroy'])->name('floor-lines.destroy');

		Route::get('/masters/{entity}', [HrMasterController::class, 'index'])->name('masters.index');
		Route::get('/masters/{entity}/create', [HrMasterController::class, 'create'])->name('masters.create');
		Route::post('/masters/{entity}', [HrMasterController::class, 'store'])->name('masters.store');
		Route::get('/masters/{entity}/{id}/edit', [HrMasterController::class, 'edit'])->name('masters.edit');
		Route::put('/masters/{entity}/{id}', [HrMasterController::class, 'update'])->name('masters.update');
		Route::delete('/masters/{entity}/{id}', [HrMasterController::class, 'destroy'])->name('masters.destroy');

		// Regular to Weekend
		Route::get('/regular-to-weekend', [RegularToWeekendController::class, 'index'])->name('regular-to-weekend.index');
		Route::post('/regular-to-weekend', [RegularToWeekendController::class, 'store'])->name('regular-to-weekend.store');
		Route::put('/regular-to-weekend/{id}', [RegularToWeekendController::class, 'update'])->name('regular-to-weekend.update');

		// Production Rate
		Route::get('/production-rate', [ProductionRateController::class, 'index'])->name('production-rate.index');
		Route::get('/production-rate/create', [ProductionRateController::class, 'create'])->name('production-rate.create');
		Route::post('/production-rate', [ProductionRateController::class, 'store'])->name('production-rate.store');
		Route::get('/production-rate/{id}/edit', [ProductionRateController::class, 'edit'])->name('production-rate.edit');
		Route::put('/production-rate/{id}', [ProductionRateController::class, 'update'])->name('production-rate.update');
		Route::delete('/production-rate/{id}', [ProductionRateController::class, 'destroy'])->name('production-rate.destroy');
		Route::get('/production-rate/{id}/assign-progress', [ProductionRateController::class, 'assignProgress']);
		Route::post('/production-rate/{id}/assign-progress', [ProductionRateController::class, 'assignProgress'])->name('production-rate.assign-progress');

			// Roster Management
		Route::get('/rosters', [RosterController::class, 'index'])->name('rosters.index');
		Route::get('/rosters/create', [RosterController::class, 'create'])->name('rosters.create');
		Route::post('/rosters', [RosterController::class, 'store'])->name('rosters.store');
		Route::delete('/rosters/{id}', [RosterController::class, 'destroy'])->name('rosters.destroy');


		Route::get('/zkteco-data-import',[AttendanceMachineController::class,'import'])->name('importZkteco');
		Route::post('/import-zkteco-data',[AttendanceMachineController::class,'importAction'])->name('importZktecoAction');
		Route::get('/machine-logs', [AttendanceMachineController::class, 'logs'])->name('machine-logs.index');
	});

// Machine API — token-protected, no session middleware
Route::middleware(['api', 'hr.machine'])
    ->prefix('api/hr-machine')
    ->name('hr.machine.')
    ->group(function () {
        Route::post('/data', [AttendanceMachineController::class, 'receiveData'])->name('data');
        Route::post('/bulk', [AttendanceMachineController::class, 'receiveBulkData'])->name('bulk');
        Route::post('/adms-records', [AttendanceMachineController::class, 'receiveAdmsRecords'])->name('adms-records');
        Route::get('/fetch-employee', [AttendanceMachineController::class, 'fetchEmployees'])->name('fetch-employee');
    });


