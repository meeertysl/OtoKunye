<?php

use App\Http\Controllers\ServiceRecordController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminAuditLogController;
use App\Http\Controllers\AdminBackupController;
use App\Http\Controllers\AdminBranchController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\PartController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect('/vehicles') : redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
});

Route::get('/musteri/arac/{uuid}', [VehicleController::class, 'publicShow'])->name('vehicles.public.show');
Route::get('/musteri-portal/giris', [CustomerPortalController::class, 'showLoginForm'])->name('customer.portal.login');
Route::post('/musteri-portal/giris', [CustomerPortalController::class, 'login'])->name('customer.portal.login.attempt');
Route::get('/qr/{uuid}', [QrController::class, 'scan'])->name('qr.scan');
Route::post('/musteri/arac/{uuid}/service-records/{serviceRecord}/onay', [ServiceRecordController::class, 'approveFromPublic'])
    ->name('service-records.public-approve');

Route::middleware('customer.portal')->group(function () {
    Route::post('/musteri-portal/cikis', [CustomerPortalController::class, 'logout'])->name('customer.portal.logout');
    Route::get('/musteri-portal/araclarim', [CustomerPortalController::class, 'vehicles'])->name('customer.portal.vehicles');
    Route::get('/musteri-portal/araclarim/{vehicle}', [CustomerPortalController::class, 'showVehicle'])->name('customer.portal.vehicle.show');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    Route::get('/arac/{vehicle}/etiket-yazdir', [VehicleController::class, 'printLabel'])->name('vehicles.print-label');

    // Usta, secilen araca yeni bakim kaydi olusturur.
    Route::post('/vehicles/{vehicle}/service-records', [ServiceRecordController::class, 'store'])
        ->name('vehicles.service-records.store');
    Route::get('/vehicles/{vehicle}/service-records/{serviceRecord}/edit', [ServiceRecordController::class, 'edit'])
        ->name('vehicles.service-records.edit');
    Route::put('/vehicles/{vehicle}/service-records/{serviceRecord}', [ServiceRecordController::class, 'update'])
        ->name('vehicles.service-records.update');
    Route::delete('/vehicles/{vehicle}/service-records/{serviceRecord}', [ServiceRecordController::class, 'destroy'])
        ->name('vehicles.service-records.destroy');
    Route::post('/vehicles/{vehicle}/service-records/{serviceRecord}/invoice-sync', [ServiceRecordController::class, 'requestInvoiceSync'])
        ->name('vehicles.service-records.invoice-sync');
    Route::post('/notifications/{notification}/read', [UserNotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/reports/print', [ReportController::class, 'printView'])->name('reports.print');
    Route::get('/parts', [PartController::class, 'index'])->name('parts.index');
    Route::post('/parts', [PartController::class, 'store'])->name('parts.store');
    Route::post('/parts/{part}/adjust-stock', [PartController::class, 'adjustStock'])->name('parts.adjust-stock');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/branches', [AdminBranchController::class, 'index'])->name('branches.index');
    Route::post('/branches', [AdminBranchController::class, 'store'])->name('branches.store');
    Route::put('/branches/{branch}', [AdminBranchController::class, 'update'])->name('branches.update');
    Route::put('/branches/{branch}/toggle-status', [AdminBranchController::class, 'toggleStatus'])->name('branches.toggle-status');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/backups', [AdminBackupController::class, 'index'])->name('backups.index');
    Route::post('/backups/create', [AdminBackupController::class, 'create'])->name('backups.create');
    Route::post('/backups/{file}/restore', [AdminBackupController::class, 'restore'])->name('backups.restore');
    Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update-role');
    Route::put('/users/{user}/branch', [AdminUserController::class, 'updateBranch'])->name('users.update-branch');
    Route::put('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
});
