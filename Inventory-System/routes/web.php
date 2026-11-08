<?php

use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::get('/adjustments/create', [StockAdjustmentController::class, 'create'])->name('adjustments.create');
    Route::post('/adjustments', [StockAdjustmentController::class, 'store'])->name('adjustments.store');
    Route::get('/api/products/{product}/quantity', [StockAdjustmentController::class, 'quantity'])->name('api.products.quantity');
 
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
    Route::resource('products', ProductController::class);
    Route::resource('transactions', InventoryTransactionController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Part 7: JSON endpoints for the dashboard's AJAX polling (session-authenticated)
    Route::prefix('api')->group(function () {
        Route::get('/dashboard/stats', [DashboardApiController::class, 'stats'])->name('api.dashboard.stats');
        Route::get('/products/low-stock', [DashboardApiController::class, 'lowStock'])->name('api.products.low-stock');
        Route::get('/transactions/recent', [DashboardApiController::class, 'recentTransactions'])->name('api.transactions.recent');
    });
});

require __DIR__.'/auth.php';