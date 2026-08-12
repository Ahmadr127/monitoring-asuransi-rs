<?php

use App\Http\Controllers\Admin\BadanUsahaController;
use App\Http\Controllers\Admin\ContractRolloverController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DiskonController;
use App\Http\Controllers\Admin\ExportImportController;
use App\Http\Controllers\Admin\JenisLayananController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PenagihanPembayaranController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReminderRecipientController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RuangLingkupController;
use App\Http\Controllers\Admin\TableSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkflowController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\KerjasamaController as UserKerjasamaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasPermission('approval') && ! $user->hasPermission('kerjasama')) {
            return redirect()->route('user.dashboard');
        }

        if ($user->hasPermission('kerjasama')) {
            return redirect()->route('admin.ruang-lingkup.index');
        }

        return redirect()->route('user.kerjasama.index');
    }

    return redirect()->route('login');
});

// Public Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated Routes
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->middleware('permission:dashboard')->name('dashboard');

    // Admin — Dashboard & overview (area manajemen)
    Route::middleware(['permission:users'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/kerjasama/{type}', [AdminDashboardController::class, 'kerjasamaDetail'])->name('kerjasama.detail');
    });

    // Master Data
    Route::middleware(['permission:master'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/diskon', [DiskonController::class, 'index'])->name('diskon.index');
        Route::post('/diskon', [DiskonController::class, 'store'])->name('diskon.store');
        Route::put('/diskon/{diskon}', [DiskonController::class, 'update'])->name('diskon.update');
        Route::match(['DELETE', 'POST'], '/diskon/{diskon}', [DiskonController::class, 'destroy'])->name('diskon.destroy');

        Route::get('/penagihan-pembayaran', [PenagihanPembayaranController::class, 'index'])->name('penagihan-pembayaran.index');
        Route::post('/penagihan-pembayaran', [PenagihanPembayaranController::class, 'store'])->name('penagihan-pembayaran.store');
        Route::put('/penagihan-pembayaran/{penagihanPembayaran}', [PenagihanPembayaranController::class, 'update'])->name('penagihan-pembayaran.update');
        Route::match(['DELETE', 'POST'], '/penagihan-pembayaran/{penagihanPembayaran}', [PenagihanPembayaranController::class, 'destroy'])->name('penagihan-pembayaran.destroy');

        Route::get('/master-jenis-layanan', [JenisLayananController::class, 'index'])->name('master-jenis-layanan.index');
        Route::post('/master-jenis-layanan', [JenisLayananController::class, 'store'])->name('master-jenis-layanan.store');
        Route::put('/master-jenis-layanan/{masterJenisLayanan}', [JenisLayananController::class, 'update'])->name('master-jenis-layanan.update');
        Route::match(['DELETE', 'POST'], '/master-jenis-layanan/{masterJenisLayanan}', [JenisLayananController::class, 'destroy'])->name('master-jenis-layanan.destroy');

        Route::get('/master-obat', [ObatController::class, 'index'])->name('master-obat.index');
        Route::post('/master-obat', [ObatController::class, 'store'])->name('master-obat.store');
        Route::put('/master-obat/{obat}', [ObatController::class, 'update'])->name('master-obat.update');
        Route::match(['DELETE', 'POST'], '/master-obat/{obat}', [ObatController::class, 'destroy'])->name('master-obat.destroy');

        Route::get('/master-badan-usaha', [BadanUsahaController::class, 'index'])->name('master-badan-usaha.index');
        Route::post('/master-badan-usaha', [BadanUsahaController::class, 'store'])->name('master-badan-usaha.store');
        Route::put('/master-badan-usaha/{badanUsaha}', [BadanUsahaController::class, 'update'])->name('master-badan-usaha.update');
        Route::match(['DELETE', 'POST'], '/master-badan-usaha/{badanUsaha}', [BadanUsahaController::class, 'destroy'])->name('master-badan-usaha.destroy');
    });

    // Role & Permission Management
    Route::middleware(['permission:roles'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::match(['DELETE', 'POST'], '/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::match(['DELETE', 'POST'], '/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });

    // User Management
    Route::middleware(['permission:users'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/sync-simutu', [UserController::class, 'syncFromSimutu'])->name('users.sync-simutu');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::match(['DELETE', 'POST'], '/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Contract Management (rollover & history)
    Route::middleware(['permission:kerjasama'])->prefix('admin')->name('admin.')->group(function () {
        Route::post('/contracts/{contract}/rollover', [ContractRolloverController::class, 'rollover'])->name('contracts.rollover');
        Route::get('/contracts/{contract}/history', [ContractRolloverController::class, 'history'])->name('contracts.history');
        Route::post('/contracts/update-expired', [ContractRolloverController::class, 'updateExpiredContracts'])->name('contracts.update-expired');
        Route::get('/contracts/status-check', [ContractRolloverController::class, 'statusCheck'])->name('contracts.status-check');
    });

    // Departemen / organisasi
    Route::middleware(['permission:departemen'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/types', [DepartmentController::class, 'types'])->name('departments.types.index');
        Route::post('/departments/types', [DepartmentController::class, 'storeType'])->name('departments.types.store');
        Route::put('/departments/types/{type}', [DepartmentController::class, 'updateType'])->name('departments.types.update');
        Route::match(['DELETE', 'POST'], '/departments/types/{type}', [DepartmentController::class, 'destroyType'])->name('departments.types.destroy');
        Route::get('/departments/{unit}', [DepartmentController::class, 'show'])->name('departments.show');
        Route::get('/departments/{unit}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/departments/{unit}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::post('/departments/{unit}/members', [DepartmentController::class, 'addMember'])->name('departments.add-member');
        Route::match(['DELETE', 'POST'], '/departments/{unit}/members/{user}', [DepartmentController::class, 'removeMember'])->name('departments.remove-member');
        Route::patch('/departments/{unit}/head', [DepartmentController::class, 'updateHead'])->name('departments.update-head');
        Route::match(['DELETE', 'POST'], '/departments/{unit}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    });

    // Workflow approval (definisi & step dinamis)
    Route::middleware(['permission:workflow'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/workflows', [WorkflowController::class, 'index'])->name('workflows.index');
        Route::post('/workflows', [WorkflowController::class, 'store'])->name('workflows.store');
        Route::get('/workflows/{workflow}/edit', [WorkflowController::class, 'edit'])->name('workflows.edit');
        Route::put('/workflows/{workflow}', [WorkflowController::class, 'update'])->name('workflows.update');
        Route::match(['DELETE', 'POST'], '/workflows/{workflow}', [WorkflowController::class, 'destroy'])->name('workflows.destroy');
        Route::post('/workflows/{workflow}/steps', [WorkflowController::class, 'storeStep'])->name('workflows.steps.store');
        Route::post('/workflows/steps/{step}/update', [WorkflowController::class, 'updateStep'])->name('workflows.steps.update');
        Route::post('/workflows/steps/{step}/destroy', [WorkflowController::class, 'destroyStep'])->name('workflows.steps.destroy');
    });

    // Settings
    Route::middleware(['permission:pengaturan'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/table-settings', [TableSettingController::class, 'index'])->name('table-settings.index');
        Route::post('/table-settings', [TableSettingController::class, 'update'])->name('table-settings.update');
        Route::get('/table-settings/get', [TableSettingController::class, 'getSettings'])->name('table-settings.get');
    });

    Route::middleware(['permission:reminder'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/reminder-recipients', [ReminderRecipientController::class, 'index'])->name('reminder-recipients.index');
        Route::post('/reminder-recipients', [ReminderRecipientController::class, 'store'])->name('reminder-recipients.store');
        Route::put('/reminder-recipients/{recipient}', [ReminderRecipientController::class, 'update'])->name('reminder-recipients.update');
        Route::match(['DELETE', 'POST'], '/reminder-recipients/{recipient}', [ReminderRecipientController::class, 'destroy'])->name('reminder-recipients.destroy');
        Route::patch('/reminder-recipients/{recipient}/toggle', [ReminderRecipientController::class, 'toggleStatus'])->name('reminder-recipients.toggle');
        Route::post('/reminder-recipients/{recipient}/send-manual', [ReminderRecipientController::class, 'sendManual'])->name('reminder-recipients.send-manual');
        Route::get('/reminder-recipients/{recipient}', fn () => redirect()->route('admin.reminder-recipients.index'))->name('reminder-recipients.show');
    });

    // Export & Import (laporan)
    Route::middleware(['permission:laporan'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/export/xlsx', [ExportImportController::class, 'exportXlsx'])->name('export.xlsx');
        Route::get('/export/csv', [ExportImportController::class, 'exportCsv'])->name('export.csv');
        Route::get('/export/pdf', [ExportImportController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/pdf/{rl}', [ExportImportController::class, 'exportSinglePdf'])->name('export.single-pdf');
        Route::get('/export/import', [ExportImportController::class, 'importForm'])->name('export.import-form');
        Route::post('/export/import/preview', [ExportImportController::class, 'previewImport'])->name('export.import.preview');
        Route::post('/export/import/confirm', [ExportImportController::class, 'confirmImport'])->name('export.import.confirm');
        Route::get('/export/template', [ExportImportController::class, 'downloadTemplate'])->name('export.template');
    });

    // Notifications
    Route::middleware(['permission:notifikasi'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    });

    // Ruang Lingkup (Kerjasama) — kelola kontrak
    Route::middleware(['permission:kerjasama'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/ruang-lingkup', [RuangLingkupController::class, 'index'])->name('ruang-lingkup.index');
        Route::get('/ruang-lingkup/create', [RuangLingkupController::class, 'create'])->name('ruang-lingkup.create');
        Route::post('/ruang-lingkup', [RuangLingkupController::class, 'store'])->name('ruang-lingkup.store');
        Route::get('/ruang-lingkup/master-data', [RuangLingkupController::class, 'masterData'])->name('ruang-lingkup.master-data');
        Route::get('/ruang-lingkup/{ruangLingkup}', [RuangLingkupController::class, 'show'])->name('ruang-lingkup.show');
        Route::get('/ruang-lingkup/{ruangLingkup}/edit', [RuangLingkupController::class, 'edit'])->name('ruang-lingkup.edit');
        Route::put('/ruang-lingkup/{ruangLingkup}', [RuangLingkupController::class, 'update'])->name('ruang-lingkup.update');
        Route::match(['DELETE', 'POST'], '/ruang-lingkup/{ruangLingkup}', [RuangLingkupController::class, 'destroy'])->name('ruang-lingkup.destroy');
        Route::post('/ruang-lingkup/{ruangLingkup}/submit', [RuangLingkupController::class, 'submit'])->name('ruang-lingkup.submit');
    });

    // Approval — antrean & keputusan (approver), detail & cancel boleh oleh pengaju
    Route::middleware(['permission:approval'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    Route::middleware(['approval.access'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/approvals/{approval}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{approval}/cancel', [ApprovalController::class, 'cancel'])->name('approvals.cancel');
    });

    // User Module (baca kerjasama)
    Route::middleware(['permission:kerjasama_read'])->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/kerjasama', [UserKerjasamaController::class, 'index'])->name('kerjasama.index');
        Route::get('/kerjasama/detail/{ruangLingkup}', [UserKerjasamaController::class, 'show'])->name('kerjasama.show')->where('ruangLingkup', '[0-9]+');
        Route::get('/kerjasama/export/csv', [UserKerjasamaController::class, 'exportCsv'])->name('kerjasama.export-csv');
        Route::get('/kerjasama/{type}', [UserDashboardController::class, 'kerjasamaDetail'])->name('kerjasama.detail');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});