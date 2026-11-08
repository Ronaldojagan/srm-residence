<?php

use App\Enums\UserType;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BannerController;
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

Route::get('/ping', fn() => response()->json(['status' => 'alive', 'app' => 'SRM RESIDENCE', 'timestamp' => now()->toIso8601String()]));
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
        Route::get('/stats', 'getStats')->name('stats');
        Route::get('/export', 'export')->name('export');
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
        Route::get('/stats', 'getStats')->name('stats');
        Route::get('/export', 'export')->name('export');
        Route::post('/', 'store')->name('store');
        Route::get('/{tenant}/view', 'view')->name('view');
        Route::get('/{tenant}/data', 'getData')->name('data');
        Route::post('/{tenant}/update', 'update')->name('update');
        Route::delete('/{tenant}', 'destroy')->name('destroy');
        Route::post('/status', 'updateStatus')->name('status');
    });

    // Rooms
    Route::controller(RoomController::class)->prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/export', 'export')->name('export');
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
        Route::get('/rooms/export', 'exportRoomExpenses')->name('rooms.export');
        Route::get('/personal', 'personalExpenses')->name('personal');
        Route::get('/personal/export', 'exportPersonalExpenses')->name('personal.export');
        Route::get('/personal/stats', 'getPersonalStatsAjax')->name('personal.stats');
        Route::post('/', 'store')->name('store');
        Route::get('/{expense}/data', 'getData')->name('data');
        Route::post('/{expense}/update', 'update')->name('update');
        Route::delete('/{expense}', 'destroy')->name('destroy');
    });

    // Loans & EMI Management
    Route::controller(LoanController::class)->prefix('loans')->name('loans.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/stats', 'getStatsAjax')->name('stats');
        Route::post('/', 'store')->name('store');
        Route::get('/{loan}/data', 'getData')->name('data');
        Route::post('/{loan}/update', 'update')->name('update');
        Route::delete('/{loan}', 'destroy')->name('destroy');
        Route::get('/{loan}/schedule', 'getSchedule')->name('schedule');
        Route::post('/{loan}/pay-emi', 'recordEmi')->name('pay_emi');
        Route::delete('/emis/{emi}', 'destroyEmi')->name('destroy_emi');
    });

    // Bills & Payments
    Route::controller(BillingController::class)->prefix('billing')->name('billing.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/stats', 'getStats')->name('stats');
        Route::get('/bills-data', 'billsData')->name('bills.data');
        Route::get('/bills/export', 'exportBills')->name('bills.export');
        Route::get('/payments-data', 'paymentsData')->name('payments.data');
        Route::get('/payments/export', 'exportPayments')->name('payments.export');
        Route::post('/bills', 'storeBill')->name('bills.store');
        Route::post('/bills/quick-monthly', 'quickGenerateMonthlyRent')->name('bills.quick_monthly');
        Route::get('/bills/{id}', 'getBillDetails')->name('bills.details');
        Route::get('/bills/{id}/print', 'printInvoice')->name('bills.print');
        Route::post('/bills/{id}/send-email', 'sendInvoiceEmail')->name('bills.send_email');
        Route::get('/tenants/{id}/billing-info', 'getTenantBillingInfo')->name('tenant_info');
        Route::post('/bills/{id}/update', 'updateBill')->name('bills.update');
        Route::delete('/bills/{id}', 'destroyBill')->name('bills.destroy');
        Route::post('/payments', 'storePayment')->name('payments.store');
        Route::get('/payments/{id}', 'getPaymentDetails')->name('payments.details');
        Route::get('/payments/{id}/print', 'printReceipt')->name('payments.print');
        Route::delete('/payments/{id}', 'destroyPayment')->name('payments.destroy');
    });

    // Reports
    Route::controller(ReportController::class)->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/data', 'getReportData')->name('data');
        Route::get('/table', 'getReportTableData')->name('table');
        Route::get('/summary', 'reportSummary')->name('summary');
        Route::get('/charts', 'reportCharts')->name('charts');
        Route::get('/export', 'exportReport')->name('export');
    });

    // Settings
    Route::controller(SettingsController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'settingsIndex')->name('index');
        Route::post('/save', 'saveSettings')->name('save');
    });

    // Banners & Media
    Route::controller(BannerController::class)->prefix('banner')->name('banner.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{banner}/edit', 'edit')->name('edit');
        Route::put('/{banner}', 'update')->name('update');
        Route::delete('/{banner}', 'destroy')->name('destroy');
        Route::post('/status', 'updateStatus')->name('status');
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
