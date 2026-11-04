<?php

use App\Enums\UserType;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CancellationReasonController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DeliveryManController;
use App\Http\Controllers\DeliveryPointsController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductRequestController;
use App\Http\Controllers\ReplaceController;
use App\Http\Controllers\ReplaceOrderReasonController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ReturnOrderReasonController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\UserAccountReasonController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MissingBottleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes();

Route::get('/', fn() => view('auth.login'));

Route::get('/product/{id}', function ($id) {
    $playStoreUrl = "https://play.google.com/store/apps/details?id=com.webstormers.alokha_user";

    return response()->make("
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Open App</title>
            <script>
                // Try to open app via intent link
                window.location = 'intent://product/{$id}#Intent;scheme=https;package=com.webstormers.alokha_user;end';

                // Fallback to Play Store after 1.5s if app not installed
                setTimeout(function() {
                    window.location = '{$playStoreUrl}';
                }, 1500);
            </script>
        </head>
        <body>
            <p>Redirecting...</p>
        </body>
        </html>
    ", 200, ['Content-Type' => 'text/html']);
});

Route::get('/user-data-remove', fn() => view('backend.user_data'));
Route::post('/user-data-remove', [SettingsController::class, 'requestUserDelete']);

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
Route::get('invoice/{id}', [FilterController::class, 'invoice'])->name('invoice.download');
Route::get('orders/complete/{id}/{code}', [OrderController::class, 'showOrder']);
Route::post('orders/{id}/collect-cash', [OrderController::class, 'collectCash'])->name('order.collect-cash');
Route::post('orders/complete/{id}/{code}', [OrderController::class, 'orderComplete'])->name('order.complete');
Route::post('/orders/{id}/save-verification', [OrderController::class, 'saveDeliveryVerification']);

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'user-access:' . UserType::SUPERADMIN]], function () {

    Route::controller(ReturnController::class)->prefix('returns')->name('returns.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/pending', 'pending')->name('pending');
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::get('/{orderItem}/details', 'details')->name('details');
        Route::post('/admin-verify', 'adminVerify')->name('admin-verify');
        Route::post('/shop-verify', 'shopVerify')->name('shop-verify');
        Route::post('/final-approve', 'finalApprove')->name('final-approve');
        Route::post('/process-refund', 'processRefund')->name('process-refund');
        Route::post('/quick-approve', 'quickApprove')->name('quick-approve');
        Route::post('/quick-reject', 'quickReject')->name('quick-reject');
    });

    Route::controller(ReplaceController::class)->prefix('replaces')->name('replaces.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/pending', 'pending')->name('pending');
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::get('/{orderItem}/details', 'details')->name('details');
        Route::get('/search-products', 'searchReplacementProducts')->name('search-products');
        Route::post('/admin-verify', 'adminVerify')->name('admin-verify');
        Route::post('/shop-verify', 'shopVerify')->name('shop-verify');
        Route::post('/final-approve', 'finalApprove')->name('final-approve');
        Route::post('/select-product', 'selectReplacementProduct')->name('select-product');
        Route::post('/create-order', 'createReplacementOrder')->name('create-order');
        Route::post('/quick-approve', 'quickApprove')->name('quick-approve');
        Route::post('/quick-reject', 'quickReject')->name('quick-reject');
    });

    Route::controller(FilterController::class)->prefix('filters')->name('filters.')->group(function () {
        Route::get('/', 'index');
        Route::get('/{save_filter}', 'show');
        Route::post('/', 'store');
        Route::put('/{save_filter}', 'update');
        Route::delete('/{save_filter}', 'destroy');
    });

    Route::controller(HomeController::class)->group(function () {
        Route::get('/dashboard', 'index');
        Route::get('/orders-data', 'getOrders');
        Route::get('/user-data', 'getUser');
        Route::get('/analytics-data', 'getAnalyticsData');
        Route::get('/today-activity-data', 'getTodayActivityData');
    });

    Route::controller(PackageController::class)->prefix('package')->name('package.')->group(function () {
        Route::get('/create', 'create');
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{package}/edit', 'edit');
        Route::put('/{package}', 'update');
        Route::delete('/{package}', 'destroy');
        Route::get('/{package}', 'view');
        Route::post('/bulk-status', 'bulkStatus');
        Route::delete('/bulk-delete', 'bulkDelete');
        Route::post('/update-order', 'updateOrder');
        Route::post('/status', 'updateStatus');
    });

    Route::controller(DeliveryPointsController::class)->prefix('delivery-points')->name('delivery-points.')->group(function () {
        Route::get('/create', 'create');
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{deliveryPoint}/edit', 'edit');
        Route::put('/{deliveryPoint}', 'update');
        Route::get('/{deliveryPoint}', 'view');
        Route::post('/bulk-status', 'bulkStatus');
        Route::delete('/bulk-delete', 'bulkDelete');
        Route::delete('/{deliveryPoint}', 'destroy');
        Route::post('/update-order', 'updateOrder');
        Route::post('/status', 'updateStatus');
    });

    Route::controller(DeliveryManController::class)->prefix('delivery-mans')->name('delivery-mans.')->group(function () {
        Route::get('/', 'index');
        Route::get('/create', 'create');
        Route::post('/', 'store');
        Route::get('/{deliveryMan}/edit', 'edit');
        Route::put('/{deliveryMan}', 'update');
        Route::delete('/{deliveryMan}', 'destroy');
        Route::get('/{deliveryMan}/view', 'view');
        Route::get('/orders/{deliveryMan}', 'getUserOrders');
        Route::get('/stats/{deliveryMan}', 'getUserStats');
        Route::post('/status', 'updateStatus');
    });

    Route::controller(MissingBottleController::class)->prefix('bottle-missing')->name('bottle-missing.')->group(function () {
        Route::get('/', 'index');
        Route::delete('/{orderId}', 'destroy');
        Route::get('/{orderId}/view', 'view');
        Route::get('/list/{order}', 'orderMissingBottleList');
    });

    Route::controller(SubCategoryController::class)->prefix('sub-category')->name('sub-category.')->group(function () {
        Route::get('/create/{category}', 'create');
        Route::get('/{category}', 'index');
        Route::post('/', 'store');
        Route::get('/{sub_category}/edit', 'edit');
        Route::put('/{sub_category}', 'update');
        Route::delete('/{sub_category}', 'destroy');
        Route::post('/bulk-status', 'bulkStatus');
        Route::delete('/bulk-delete', 'bulkDelete');
        Route::post('/update-order', 'updateOrder');
        Route::post('/status', 'updateStatus');
    });

    Route::controller(CouponController::class)->prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/create', 'create');
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{coupon}/edit', 'edit');
        Route::put('/{coupon}', 'update');
        Route::delete('/{coupon}', 'destroy');
        Route::get('/{coupon}', 'view');
        Route::post('/status', 'updateStatus');
    });

    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{user}/data', 'getUserData');
        Route::get('/{user}/edit', 'edit');
        Route::put('/{user}', 'update');
        Route::post('/{user}/update', 'update');
        Route::delete('/{user}', 'destroy');
        Route::get('/{user}/view', 'view');
        Route::get('/orders/{user}', 'getUserOrders');
        Route::get('/stats/{user}', 'getUserStats');
        Route::post('/status', 'updateStatus');
    });

    Route::controller(TenantController::class)->prefix('tenants')->name('tenants.')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{tenant}/data', 'getData');
        Route::post('/{tenant}/update', 'update');
        Route::delete('/{tenant}', 'destroy');
        Route::post('/status', 'updateStatus');
    });

    Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
        Route::get('/counts', 'getCounts');
        Route::post('/approve', 'approve');
        Route::post('/reject', 'reject');
        Route::get('/pending-requests', 'pendingRequests');
        Route::get('/update-requests/{product_update_request}', 'getUpdateRequest');
        Route::post('/update-requests/{product_update_request}/approve', 'approveUpdateRequest');
        Route::post('/update-requests/{product_update_request}/reject', 'rejectUpdateRequest');
        Route::get('/create', 'create');
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{product}/edit', 'edit');
        Route::put('/{product}', 'update');
        Route::delete('/{product}', 'destroy');
        Route::get('/{product}', 'view');
        Route::post('/add-stock/{product}', 'addStock');
        Route::get('/stock-history/{product}', 'stockHistory');
        Route::get('/get-subcategories/{categoryId}', 'getSubcategories');
        Route::post('/status', 'updateStatus');
        Route::get('/update-requests-data', 'pendingRequests')
            ->name('admin.products.update-requests');
    });

    Route::get('/couriers/available', [OrderController::class, 'getAvailableCouriers'])->name('couriers.available');

    Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
        Route::get('/', 'index')->name('orders.index');
        Route::get('{order}', 'view')->name('orders.view');
        Route::get('invoice/{order}', 'invoice')->name('orders.invoice');
        Route::post('cancel', 'cancel')->name('cancel');
        Route::post('bulk-cancel', 'bulkCancel')->name('orders.bulk-cancel');
        Route::post('{order}/update-status', 'updateStatus')->name('orders.update-status');
        Route::post('{order}/update-payment-status', 'updatePaymentStatus')->name('orders.update-payment-status');

        Route::post('{order}/items/{orderItem}/cancel', 'cancelProduct')->name('cancel-product');
        Route::get('product-history/{orderItem}', 'getProductStatusHistory')->name('product-history');
        Route::post('{order}/partial-refund', 'processPartialRefund')->name('partial-refund');
        Route::post('update-item-status', 'updateItemStatus')->name('update-item-status');
        Route::post('update-bulk-item-status', 'updateBulkItemStatus')->name('update-bulk-item-status');
        Route::post('mark-all-delivered', 'markAllDelivered')->name('mark-all-delivered');

        Route::get('{order}/items-for-dispatch', 'getItemsForDispatch')->name('orders.items-for-dispatch');
        Route::get('{order}/dispatch', 'showDispatchForm')->name('orders.dispatch.form');
        Route::post('{order}/dispatch-items', 'dispatchOrderItems')->name('orders.dispatch-items');
        Route::post('dispatch-item', 'dispatchItem')->name('dispatch-item');
        Route::post('dispatch-bulk', 'dispatchBulk')->name('dispatch-bulk');

        Route::post('/shipments/{shipment}/mark-delivered', 'markShipmentDelivered')->name('shipments.mark-delivered');
        Route::get('/shipments/{shipment}/tracking', 'getShipmentTracking')->name('orders.shipments.tracking');

        Route::post('submit-return-request', 'submitReturnRequest')->name('orders.submit-return-request');
        Route::post('submit-replace-request', 'submitReplaceRequest')->name('submit-replace-request');

        Route::post('/return/admin-verify', 'adminVerifyReturn')->name('return.admin-verify');
        Route::post('/return/shop-verify', 'shopVerifyReturn')->name('return.shop-verify');
        Route::post('/return/admin-approve', 'adminApproveReturn')->name('return.admin-approve');
        Route::post('/return/process-refund', 'processReturnRefund')->name('return.process-refund');

        Route::post('/replace/admin-verify', 'adminVerifyReplace')->name('replace.admin-verify');
        Route::post('/replace/admin-approve', 'adminApproveReplace')->name('replace.admin-approve');

        Route::get('/dashboard', 'verificationDashboard')->name('dashboard');
        Route::get('/replace/get-details', 'getReplaceDetails')->name('replace.get-details');
        Route::get('/products/available-for-replacement', 'getAvailableForReplacement')->name('available-for-replacement');
        Route::get('/get-details', 'getVerificationDetails')->name('get-details');
        Route::get('/get-status', 'getVerificationStatus')->name('get-status');
    });

    Route::controller(ExportController::class)->group(function () {
        Route::get('/orders-export', 'exportOrders');
        Route::get('/orders/export-single/{order}', 'exportSingle');
    });

    Route::controller(ShopController::class)->prefix('shops')->name('shops.')->group(function () {
        Route::get('/create', 'create');
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{shop}/edit', 'edit');
        Route::put('/{shop}', 'update');
        Route::delete('/{shop}', 'destroy');
        Route::get('/{shop}', 'view');
        Route::get('/{shop}/stats', 'getShopStats');
        Route::patch('/update-status/{shop}', 'updateShop');
        Route::get('/orders/{shop}', 'getShopOrders');
        Route::get('/products/{shop}', 'getShopProducts');
        Route::get('/order-items/{order}', 'getOrderItems');
        Route::get('/product-details/{product}', 'getProductDetails');
        Route::get('/{shop}/deactivation-history', 'deactivationHistory');
        Route::get('/{shop}/payout-stats', 'getPayoutStats');
        Route::get('/payout-details/{shop}', 'getPayoutDetails');

        Route::post('/{shop}/deactivate', 'deactivate');
        Route::post('/{shop}/reactivate', 'reactivate');
        Route::post('/products-update-status', 'updateProductStatus');
        Route::post('/status', 'updateStatus');
        Route::post('/create-for-user', 'createShopForUser');

        Route::post('/approve/{shop}', 'approveShop')->name('approve');
        Route::post('/reject/{shop}', 'rejectShop')->name('reject');
        Route::post('/unapprove/{shop}', 'unapproveShop')->name('unapprove');

        Route::get('/change-request-details/{shop_change_request}', 'getChangeRequestDetails');
        Route::post('/reject-change-request/{shop_change_request}', 'rejectChangeRequest');
        Route::post('/approve-change-request/{shop_change_request}', 'approveChangeRequest');
    });
    Route::get('/users/details/{id}', [UserController::class, 'getUserDetails']);

    Route::controller(BannerController::class)->prefix('banner')->name('banner.')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{banner}/edit', 'edit');
        Route::put('/{banner}', 'update');
        Route::delete('/{banner}', 'destroy');
        Route::post('/status', 'updateStatus');
    });
    Route::get('/get-subcategories/{category}', [BannerController::class, 'getSubcategories'])->name('admin.getSubcategories');

    Route::controller(CancellationReasonController::class)->prefix('cancellation-reason')->name('cancellation-reason.')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{cancellation_reason}/edit', 'edit');
        Route::put('/{cancellation_reason}', 'update');
        Route::delete('/{cancellation_reason}', 'destroy');
        Route::post('/status', 'updateStatus');
    });

    Route::controller(PayoutController::class)->prefix('payouts')->name('payouts.')->group(function () {
        Route::get('/', 'index');
        Route::post('/generate', 'generate');
        Route::get('/datatable', 'datatable');
        Route::get('/range-stats', 'getRangeStats')->name('payouts.range-stats');
        Route::get('/export-range', 'exportDateRange')->name('payouts.export-range');
        Route::get('/shops-datatable/{payout}', 'payoutShopsDatatable');
        Route::get('/{payout}', 'show');
        Route::post('/{payout}/mark-as-paid', 'markAsPaid');
        Route::post('/{payout}/mark-as-failed', 'markAsFailed');
        Route::get('/{payout}/export', 'export');
        Route::post('/{payoutShop}/update-status', 'updateShopStatus');
        Route::post('/recalculate-stats', 'calculatePayoutStats');
    });

    Route::get('/payments', [PaymentController::class, 'index']);

    Route::controller(ReportController::class)->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', 'index');
        Route::get('/data', 'getReportData');
        Route::get('/table', 'getReportTableData');
        Route::get('/summary', 'reportSummary');
        Route::get('/charts', 'reportCharts');
        Route::get('/order-breakdown', 'orderBreakdown');
    });

    Route::controller(SettingsController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'settingsIndex');
    });

    Route::controller(SettingsController::class)->group(function () {
        Route::post('/contact', 'contact');
        Route::post('/customer', 'customer');
        Route::post('/commission', 'commission');
        Route::post('/charges', 'charges');
        Route::post('/terms-conditions', 'termsConditions');
        Route::post('/refund-policy', 'refundPolicy');
        Route::post('/delivery-charge', 'deliveryCharge');
        Route::post('/free-delivery-amount-above', 'freeDeliveryAmountAbove');
        Route::post('/platform-fee', 'platformFee');
        Route::post('/return-replace-days-settings', 'returnReplaceDaysSettings');

        Route::get('/profile', 'profile');
        Route::post('/profile', 'updateProfile');
        Route::get('/profile-edit', 'profileEdit');
        Route::get('/password', 'password');
        Route::post('/password', 'updatePassword');
    });

    Route::controller(UserAccountReasonController::class)->prefix('user-account-reason')->name('user-account-reason.')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{user_account_reason}/edit', 'edit');
        Route::put('/{user_account_reason}', 'update');
        Route::delete('/{user_account_reason}', 'destroy');
        Route::post('/status', 'updateStatus');
    });

    Route::controller(ReturnOrderReasonController::class)->prefix('return-order-reason')->name('return-order-reason.')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{return_order_reason}/edit', 'edit');
        Route::put('/{return_order_reason}', 'update');
        Route::delete('/{return_order_reason}', 'destroy');
        Route::post('/status', 'updateStatus');
    });

    Route::controller(ReplaceOrderReasonController::class)->prefix('replace-order-reason')->name('replace-order-reason.')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{replace_order_reason}/edit', 'edit');
        Route::put('/{replace_order_reason}', 'update');
        Route::delete('/{replace_order_reason}', 'destroy');
        Route::post('/status', 'updateStatus');
    });

    Route::controller(ProductRequestController::class)->prefix('request-product')->name('product-request.')->group(function () {
        Route::get('/', 'index');
        Route::get('/products-request-details/{product_request}', 'getProductDetails');
        Route::post('/products-request-update-status/{product_request}', 'updateStatus');
    });
});

Route::get('/home', [HomeController::class, 'index'])->name('home');
