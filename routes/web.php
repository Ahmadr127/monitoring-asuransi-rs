<?php

use App\Http\Controllers\Admin\BadanUsahaController;
use App\Http\Controllers\Admin\ContractRolloverController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DiskonController;
use App\Http\Controllers\Admin\ExportImportController;
use App\Http\Controllers\Admin\JenisLayananController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PenagihanPembayaranController;
use App\Http\Controllers\Admin\ReminderRecipientController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RuangLingkupController;
use App\Http\Controllers\Admin\TableSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\KerjasamaController as UserKerjasamaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if (auth()->user()->role?->key === 'legal') {
            return redirect()->route('user.dashboard');
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
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Admin Module
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/kerjasama/{type}', [AdminDashboardController::class, 'kerjasamaDetail'])->name('kerjasama.detail');

        // Master Data
        Route::get('/ruang-lingkup', [RuangLingkupController::class, 'index'])->name('ruang-lingkup.index');
        Route::get('/ruang-lingkup/create', [RuangLingkupController::class, 'create'])->name('ruang-lingkup.create');
        Route::post('/ruang-lingkup', [RuangLingkupController::class, 'store'])->name('ruang-lingkup.store');
        Route::get('/ruang-lingkup/master-data', [RuangLingkupController::class, 'masterData'])->name('ruang-lingkup.master-data');
        Route::get('/ruang-lingkup/{ruangLingkup}', [RuangLingkupController::class, 'show'])->name('ruang-lingkup.show');
        Route::get('/ruang-lingkup/{ruangLingkup}/edit', [RuangLingkupController::class, 'edit'])->name('ruang-lingkup.edit');
        Route::put('/ruang-lingkup/{ruangLingkup}', [RuangLingkupController::class, 'update'])->name('ruang-lingkup.update');
        Route::match(['DELETE', 'POST'], '/ruang-lingkup/{ruangLingkup}', [RuangLingkupController::class, 'destroy'])->name('ruang-lingkup.destroy');

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

        // User & Role Management
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::match(['DELETE', 'POST'], '/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/sync-simutu', [UserController::class, 'syncFromSimutu'])->name('users.sync-simutu');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::match(['DELETE', 'POST'], '/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Contract Management
        Route::post('/contracts/{contract}/rollover', [ContractRolloverController::class, 'rollover'])->name('contracts.rollover');
        Route::get('/contracts/{contract}/history', [ContractRolloverController::class, 'history'])->name('contracts.history');
        Route::post('/contracts/update-expired', [ContractRolloverController::class, 'updateExpiredContracts'])->name('contracts.update-expired');
        Route::get('/contracts/status-check', [ContractRolloverController::class, 'statusCheck'])->name('contracts.status-check');

        // Settings
        Route::get('/table-settings', [TableSettingController::class, 'index'])->name('table-settings.index');
        Route::post('/table-settings', [TableSettingController::class, 'update'])->name('table-settings.update');
        Route::get('/table-settings/get', [TableSettingController::class, 'getSettings'])->name('table-settings.get');

        Route::get('/reminder-recipients', [ReminderRecipientController::class, 'index'])->name('reminder-recipients.index');
        Route::post('/reminder-recipients', [ReminderRecipientController::class, 'store'])->name('reminder-recipients.store');
        Route::put('/reminder-recipients/{recipient}', [ReminderRecipientController::class, 'update'])->name('reminder-recipients.update');
        Route::match(['DELETE', 'POST'], '/reminder-recipients/{recipient}', [ReminderRecipientController::class, 'destroy'])->name('reminder-recipients.destroy');
        Route::patch('/reminder-recipients/{recipient}/toggle', [ReminderRecipientController::class, 'toggleStatus'])->name('reminder-recipients.toggle');
        Route::post('/reminder-recipients/{recipient}/send-manual', [ReminderRecipientController::class, 'sendManual'])->name('reminder-recipients.send-manual');
        Route::get('/reminder-recipients/{recipient}', fn () => redirect()->route('admin.reminder-recipients.index'))->name('reminder-recipients.show');

        // Export & Import
        Route::get('/export/xlsx', [ExportImportController::class, 'exportXlsx'])->name('export.xlsx');
        Route::get('/export/csv', [ExportImportController::class, 'exportCsv'])->name('export.csv');
        Route::get('/export/pdf', [ExportImportController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/pdf/{rl}', [ExportImportController::class, 'exportSinglePdf'])->name('export.single-pdf');
        Route::get('/export/import', [ExportImportController::class, 'importForm'])->name('export.import-form');
        Route::post('/export/import/preview', [ExportImportController::class, 'previewImport'])->name('export.import.preview');
        Route::post('/export/import/confirm', [ExportImportController::class, 'confirmImport'])->name('export.import.confirm');
        Route::get('/export/template', [ExportImportController::class, 'downloadTemplate'])->name('export.template');
    });

    // Notifications — shared between admin & legal
    Route::middleware(['role:admin,legal'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    });

    // User Module
    Route::middleware(['role:not_admin'])->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/kerjasama', [UserKerjasamaController::class, 'index'])->name('kerjasama.index');
        Route::get('/kerjasama/detail/{ruangLingkup}', [UserKerjasamaController::class, 'show'])->name('kerjasama.show')->where('ruangLingkup', '[0-9]+');
        Route::get('/kerjasama/export/csv', [UserKerjasamaController::class, 'exportCsv'])->name('kerjasama.export-csv');
        Route::get('/kerjasama/{type}', [UserDashboardController::class, 'kerjasamaDetail'])->name('kerjasama.detail');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
