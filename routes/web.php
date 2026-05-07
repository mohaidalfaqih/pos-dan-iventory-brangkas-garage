<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SparepartController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerificationCodeController;

use App\Http\Controllers\RekapController;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'kasir') {
            return redirect()->route('pos.index');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // ADMIN ONLY
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('spareparts', SparepartController::class);
        Route::post('/spareparts/{sparepart}/add-stock', [SparepartController::class, 'addStock'])->name('spareparts.addStock');
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/export/csv', [InventoryController::class, 'exportCsv'])->name('inventory.export');
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/export', [RekapController::class, 'exportCsv'])->name('rekap.export');
    });

    // KASIR ONLY: POS
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/add', [PosController::class, 'add'])->name('pos.add');
        Route::post('/pos/inc', [PosController::class, 'inc'])->name('pos.inc');
        Route::post('/pos/dec', [PosController::class, 'dec'])->name('pos.dec');
        Route::post('/pos/remove', [PosController::class, 'remove'])->name('pos.remove');
        Route::post('/pos/clear', [PosController::class, 'clear'])->name('pos.clear');
        Route::post('/pos/start-payment', [PosController::class, 'startPayment'])->name('pos.startPayment');
        Route::post('/pos/finish', [PosController::class, 'finish'])->name('pos.finish');
        Route::post('/pos/reset-receipt', [PosController::class, 'resetReceipt'])->name('pos.resetReceipt');
    });

    // KASIR & ADMIN: Transaksi
    Route::middleware(['role:kasir,admin'])->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/export/csv', [TransactionController::class, 'exportCsv'])->name('transactions.export');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    });

    // Profile (semua role)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('/password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');

    // OTP Verification untuk ganti password & email
    Route::post('/verification/send-password-code', [VerificationCodeController::class, 'sendPasswordCode'])->name('verification.send-password-code');
    Route::post('/verification/verify-password-code', [VerificationCodeController::class, 'verifyPasswordCode'])->name('verification.verify-password-code');
    Route::put('/verification/update-password', [VerificationCodeController::class, 'updatePassword'])->name('verification.update-password');
    Route::post('/verification/send-email-code', [VerificationCodeController::class, 'sendEmailCode'])->name('verification.send-email-code');
    Route::post('/verification/verify-email-code', [VerificationCodeController::class, 'verifyEmailCode'])->name('verification.verify-email-code');
    Route::put('/verification/update-email', [VerificationCodeController::class, 'updateEmail'])->name('verification.update-email');
});

require __DIR__ . '/auth.php';