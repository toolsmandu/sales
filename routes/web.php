<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Dashboard\ChatbotController;
use App\Http\Controllers\Dashboard\CouponCodeController;
use App\Http\Controllers\Dashboard\DashboardBootstrapController;
use App\Http\Controllers\Dashboard\SiteSettingController;
use App\Http\Controllers\Dashboard\AttendanceController;
use App\Http\Controllers\Dashboard\EmployeeSettingController;
use App\Http\Controllers\Dashboard\PaymentMethodController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\SaleController;
use App\Http\Controllers\Dashboard\StockController;
use App\Http\Controllers\Dashboard\WithdrawalController;
use App\Http\Controllers\Dashboard\QrController;
use App\Http\Controllers\Dashboard\TaskController;
use App\Http\Controllers\Dashboard\UserProfileController;
use App\Http\Controllers\Dashboard\UserManagementController;
use App\Http\Controllers\Dashboard\UserLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/coupons', [CouponCodeController::class, 'index'])->name('coupons.index');
    Route::post('/coupons', [CouponCodeController::class, 'store'])->name('coupons.store');
    Route::put('/coupons/{couponCode}', [CouponCodeController::class, 'update'])->name('coupons.update');
    Route::delete('/coupons/{couponCode}', [CouponCodeController::class, 'destroy'])->name('coupons.destroy');
    Route::get('/orders', [SaleController::class, 'index'])->name('orders.index');
    Route::get('/orders/expired', [SaleController::class, 'expiredOrders'])->name('orders.expired');
    Route::get('/orders/customer/{phone}', [SaleController::class, 'customerProfile'])
        ->where('phone', '.*')
        ->name('orders.customer');
    Route::get('/qr', [QrController::class, 'index'])->name('qr.scan');
    Route::post('/qr', [QrController::class, 'store'])->name('qr.scan.store');
    Route::put('/qr/{qrCode}', [QrController::class, 'update'])->name('qr.scan.update');
    Route::delete('/qr/{qrCode}', [QrController::class, 'destroy'])->name('qr.scan.destroy');
    Route::prefix('user-logs')->name('user-logs.')->group(function (): void {
        Route::get('/', fn () => redirect()->route('user-logs.attendance'))->name('index');
        Route::get('/attendance', [UserLogController::class, 'attendance'])->name('attendance');
        Route::get('/tasks', [UserLogController::class, 'tasks'])->name('tasks');
        Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
        Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
        Route::post('/settings/{user}', [EmployeeSettingController::class, 'store'])->name('settings.store');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    });
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/keys/create', [StockController::class, 'createKeys'])->name('stock.keys.create');
    Route::post('/stock/keys', [StockController::class, 'store'])->name('stock.keys.store');
    Route::post('/stock/keys/{stockKey}/reveal', [StockController::class, 'reveal'])->name('stock.keys.reveal');
    Route::put('/stock/keys/{stockKey}', [StockController::class, 'update'])->name('stock.keys.update');
    Route::delete('/stock/keys/{stockKey}', [StockController::class, 'destroy'])->name('stock.keys.destroy');
    Route::prefix('chatbot')->name('chatbot.')->group(function (): void {
        Route::get('/', [ChatbotController::class, 'knowledge'])->name('knowledge');
        Route::get('/existing', [ChatbotController::class, 'existing'])->name('existing');
        Route::get('/simulator', [ChatbotController::class, 'simulator'])->name('simulator');
        Route::post('/entries', [ChatbotController::class, 'store'])->name('entries.store');
        Route::get('/entries/{chatbotEntry}/edit', [ChatbotController::class, 'edit'])->name('entries.edit');
        Route::put('/entries/{chatbotEntry}', [ChatbotController::class, 'update'])->name('entries.update');
        Route::delete('/entries/{chatbotEntry}', [ChatbotController::class, 'destroy'])->name('entries.destroy');
    });
    Route::view('/payments', 'payments.index')->name('payments.index');
    Route::get('/payments/balance', [PaymentMethodController::class, 'balance'])->name('payments.balance');
    Route::get('/payments/statements', [PaymentMethodController::class, 'statements'])->name('payments.statements');
    Route::get('/payments/manage', [PaymentMethodController::class, 'index'])->name('payments.manage');
    Route::get('/payments/withdraw', [WithdrawalController::class, 'index'])->name('payments.withdraw');
    Route::get('/dashboard', fn () => redirect()->route('products.index'))->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function (): void {
    Route::get('/bootstrap', DashboardBootstrapController::class)->name('bootstrap');

    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
    Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');

    Route::post('/orders', [SaleController::class, 'store'])->name('orders.store');
    Route::put('/orders/{sale}', [SaleController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{sale}', [SaleController::class, 'destroy'])->name('orders.destroy');

    Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');

    Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::put('/users/{user}/password', [UserManagementController::class, 'updatePassword'])->name('users.password.update');

    Route::post('/settings/registration', [SiteSettingController::class, 'updateRegistration'])->name('settings.registration');
});
