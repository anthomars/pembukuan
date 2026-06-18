<?php

use App\Livewire\Products\ProductManager;
use App\Livewire\Expenses\ExpenseManager;
use App\Livewire\Reports\ReportDashboard;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportExportController;
use App\Livewire\Sales\SaleHistory;
use App\Livewire\Sales\SaleEntry;
use App\Livewire\Dashboard;
use App\Livewire\Users\UserManager;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class);
    Route::get('/produk', ProductManager::class);
    Route::get('/penjualan/{sale?}', SaleEntry::class)->whereNumber('sale');
    Route::get('/transaksi', SaleHistory::class);
    Route::get('/users', UserManager::class)->middleware('role:owner,admin');
    Route::get('/laporan', ReportDashboard::class);
    Route::get('/laporan/export', [ReportExportController::class, 'download']);
    Route::get('/pengeluaran', ExpenseManager::class);
});
