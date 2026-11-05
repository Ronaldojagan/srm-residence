<?php

use App\Enums\UserType;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SRM RESIDENCE
|--------------------------------------------------------------------------
|
| Residence management web routes.
|
*/

Auth::routes();

Route::get('/', fn() => view('auth.login'));
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'user-access:' . UserType::SUPERADMIN]], function () {

    // Dashboard
    Route::controller(HomeController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/user-data', 'getUser');
        Route::get('/analytics-data', 'getAnalyticsData');
        Route::get('/today-activity-data', 'getTodayActivityData');
    });

    // Users
    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{user}/data', 'getUserData')->name('data');
        Route::get('/{user}/edit', 'edit')->name('edit');
        Route::put('/{user}', 'update')->name('update.put');
        Route::post('/{user}/update', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
        Route::get('/{user}/view', 'view')->name('view');
        Route::get('/orders/{user}', 'getUserOrders');
        Route::get('/stats/{user}', 'getUserStats');
        Route::post('/status', 'updateStatus')->name('status');
    });
    Route::get('/users/details/{id}', [UserController::class, 'getUserDetails']);

    // Tenants
    Route::controller(TenantController::class)->prefix('tenants')->name('tenants.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{tenant}/data', 'getData')->name('data');
        Route::post('/{tenant}/update', 'update')->name('update');
        Route::delete('/{tenant}', 'destroy')->name('destroy');
        Route::post('/status', 'updateStatus')->name('status');
    });

    // Rooms
    Route::controller(RoomController::class)->prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{room}/data', 'getData')->name('data');
        Route::post('/{room}/update', 'update')->name('update');
        Route::delete('/{room}', 'destroy')->name('destroy');
        Route::post('/status', 'updateStatus')->name('status');
        Route::post('/occupancy', 'updateOccupancy')->name('occupancy');
    });

    // Expenses
    Route::controller(ExpenseController::class)->prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/rooms', 'roomExpenses')->name('rooms');
        Route::get('/personal', 'personalExpenses')->name('personal');
        Route::post('/', 'store')->name('store');
        Route::get('/{expense}/data', 'getData')->name('data');
        Route::post('/{expense}/update', 'update')->name('update');
        Route::delete('/{expense}', 'destroy')->name('destroy');
    });

    // Bills & Payments
    Route::controller(BillingController::class)->prefix('billing')->name('billing.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/bills-data', 'billsData')->name('bills.data');
        Route::get('/payments-data', 'paymentsData')->name('payments.data');
        Route::post('/bills', 'storeBill')->name('bills.store');
        Route::post('/bills/quick-monthly', 'quickGenerateMonthlyRent')->name('bills.quick_monthly');
        Route::get('/bills/{id}', 'getBillDetails')->name('bills.details');
        Route::post('/bills/{id}/update', 'updateBill')->name('bills.update');
        Route::delete('/bills/{id}', 'destroyBill')->name('bills.destroy');
        Route::post('/payments', 'storePayment')->name('payments.store');
        Route::get('/payments/{id}', 'getPaymentDetails')->name('payments.details');
        Route::delete('/payments/{id}', 'destroyPayment')->name('payments.destroy');
    });

    // Reports
    Route::controller(ReportController::class)->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'getReportData');
        Route::get('/table', 'getReportTableData');
        Route::get('/summary', 'reportSummary');
        Route::get('/charts', 'reportCharts');
    });

    // Settings
    Route::controller(SettingsController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'settingsIndex')->name('index');
    });

    Route::controller(SettingsController::class)->group(function () {
        Route::post('/contact', 'contact')->name('settings.contact');
        Route::post('/customer', 'customer')->name('settings.customer');
        Route::post('/terms-conditions', 'termsConditions')->name('settings.terms');
        Route::post('/refund-policy', 'refundPolicy')->name('settings.refund');

        Route::get('/profile', 'profile')->name('profile');
        Route::post('/profile', 'updateProfile')->name('profile.update');
        Route::get('/profile-edit', 'profileEdit')->name('profile.edit');
        Route::get('/password', 'password')->name('password.view');
        Route::post('/password', 'updatePassword')->name('password.update');
    });
});
