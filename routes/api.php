<?php

use App\Http\Controllers\Api\CommonController;

use App\Http\Controllers\Api\Shop\AuthController as ShopAuthController;
use App\Http\Controllers\Api\Shop\CommonController as ShopCommonController;
use App\Http\Controllers\Api\Shop\PayoutController as ShopPayoutController;
use App\Http\Controllers\Api\Shop\OrderController as ShopOrderController;
use App\Http\Controllers\Api\Shop\ProductController as ShopProductController;

use App\Http\Controllers\Api\User\RazorpayController;
use App\Http\Controllers\Api\User\AuthController as UserAuthController;
use App\Http\Controllers\Api\User\CommonController as UserCommonController;
use App\Http\Controllers\Api\User\ProductController as UserProductController;
use App\Http\Controllers\Api\User\OrderController as UserOrderController;
use App\Http\Controllers\Api\User\CategoryController as UserCategoryController;
use App\Http\Controllers\RazorpayWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('razorpay', [UserAuthController::class, 'razorpay'])
    ->name('razorpay');
Route::post('order-payment', [UserAuthController::class, 'payment'])
    ->name('payment');
Route::any('webhook', [RazorpayWebhookController::class, 'handleWebhook'])
    ->name('webhook');
Route::prefix('V1/user')
    ->name('user.')
    ->group(function () {
        Route::post('signin', [UserAuthController::class, 'signIn'])
            ->name('sign-in');
    });
Route::prefix('V1/delivery')
    ->name('delivery.')
    ->group(function () {
        Route::post('signin', [ShopAuthController::class, 'signIn'])
            ->name('sign-in');
    });
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::prefix('V1/user')
        ->name('user.')
        ->group(function () {
            Route::post('logout', [CommonController::class, 'logout']);
            Route::get('refresh', [CommonController::class, 'refresh']);
            Route::get('contact', [CommonController::class, 'contact']);
            Route::get('terms-conditions', [CommonController::class, 'termsConditions']);
            Route::get('return-refund-policy', [CommonController::class, 'returnRefund']);
            Route::post('delete-account', [CommonController::class, 'deleteAccount']);

            Route::get('profile', [UserCommonController::class, 'profile'])
                ->name('profile');
            Route::post('profile', [UserCommonController::class, 'profileUpdate'])
                ->name('profile_update');
            Route::get('home', [UserCommonController::class, 'home'])
                ->name('home');
            Route::get('offer-for-you/{id}/{percentage}', [UserCommonController::class, 'productsByOfferPercentage'])
                ->name('offer-for-you');
            Route::get('lowest-price-product/{id}/{amount}', [UserCommonController::class, 'productsByLowestPriceSlab'])
                ->name('lowest-price-product');
            Route::get('best-sellings', [UserCommonController::class, 'bestSelling'])
                ->name('best-sellings');
            Route::get('top-deals', [UserCommonController::class, 'topDeals'])
                ->name('top-deals');
            Route::get('latest-products', [UserCommonController::class, 'latestProducts'])
                ->name('latest-products');
            Route::post('default-address-change/{id}', [UserCommonController::class, 'defaultAddress'])
                ->name('default-address');
            Route::get('search', [UserCommonController::class, 'search'])
                ->name('search');
            Route::get('order-status', [UserCommonController::class, 'orderStatus'])
                ->name('order-status');
            Route::Post('wishlist', [UserCommonController::class, 'wishlist'])
                ->name('wishlist');
            Route::get('wishlist', [UserCommonController::class, 'wishlistShow'])
                ->name('wishlist');
            Route::get('brand', [UserCommonController::class, 'brandList'])
                ->name('brand');
            Route::get('brand/{id}', [UserCommonController::class, 'brandDetails'])
                ->name('brand');
            Route::get('categories', [UserCategoryController::class, 'categoriesWithSubcategories'])
                ->name('all_categories');
            Route::get('sub-category-products/{subCategoryId}', [UserCategoryController::class, 'getProductsBySubCategory'])
                ->name('sub-category-products');
            Route::get('packages', [UserProductController::class, 'packages'])
                ->name('packages_list');
            Route::get('products', [UserProductController::class, 'products'])
                ->name('products_list');
            Route::get('product/{productId}', [UserProductController::class, 'productDetail'])
                ->name('product_detail');
            Route::get('coupon', [UserCommonController::class, 'coupon'])
                ->name('coupon');
            Route::get('address', [UserCommonController::class, 'address'])
                ->name('address');
            Route::post('address', [UserCommonController::class, 'addressPost'])
                ->name('address_store');
            Route::get('address-edit/{id}', [UserCommonController::class, 'addressEdit'])
                ->name('address_edit');
            Route::post('address-update/{id}', [UserCommonController::class, 'addressUpdate'])
                ->name('address_update');
            Route::post('address-delete/{id}', [UserCommonController::class, 'addressDelete'])
                ->name('address_delete');
            Route::delete('cart-delete/{id}', [UserOrderController::class, 'cartDelete'])
                ->name('delete_cart');
            Route::get('cart', [UserOrderController::class, 'cartShow'])
                ->name('cart_show');
            Route::post('cart', [UserOrderController::class, 'addToCart'])
                ->name('add_to_cart');
            Route::post('single-pre-checkout/{productId}', [UserOrderController::class, 'singlePreCheckout'])
                ->name('single-pre_checkout');
            Route::post('pre-checkout', [UserOrderController::class, 'preCheckout'])
                ->name('pre_checkout');
            Route::post('order-checkout-payment', [UserOrderController::class, 'orderCheckout'])
                ->name('order_checkout');
            Route::post('order-checkout-payment-verify', [RazorpayController::class, 'paymentSuccess'])
                ->name('order_checkout_payment');
            Route::post('wallet-recharge', [RazorpayController::class, 'addMoney'])
                ->name('wallet-reacharge');
            Route::post('wallet-recharge-payment-verify', [RazorpayController::class, 'walletPaymentSuccess'])
                ->name('wallet-reacharge-verify');
            Route::get('wallet', [UserCommonController::class, 'wallet'])
                ->name('wallet');
            Route::get('notification', [UserCommonController::class, 'notification'])
                ->name('notification');
            Route::get('user-account-reason', [UserCommonController::class, 'userAccountReasons'])
                ->name('user-delete-reason');
            Route::get('return-reason', [UserCommonController::class, 'returnOrderReasons'])
                ->name('return-reason');
            Route::get('replace-reason', [UserCommonController::class, 'replaceOrderReasons'])
                ->name('replace-reason');
            Route::get('cancel-reason', [UserCommonController::class, 'cancellationReasons'])
                ->name('cancel-reason');
            Route::get('brand-select', [UserCommonController::class, 'brandSelectBoxList'])
                ->name('brand-select');
            Route::get('category-select', [UserCommonController::class, 'categorySelectBoxList'])
                ->name('category-select');

            Route::get('order', [UserOrderController::class, 'allOrder'])
                ->name('all_order');
            Route::get('order-details/{id}', [UserOrderController::class, 'orderDetails'])
                ->name('order_details');
            Route::get('invoice/{id}', [UserOrderController::class, 'invoice'])
                ->name('invoice');
            Route::post('rating/{id}', [UserOrderController::class, 'rating'])
                ->name('rating');
            Route::post('order/{order}/items/{orderItem}/cancel', [UserOrderController::class, 'cancelProduct'])
                ->name('orders.items.cancel');
            Route::post('order/{order}/items/{orderItem}/return', [UserOrderController::class, 'requestReturn']);
            Route::post('order/{order}/items/{orderItem}/replace', [UserOrderController::class, 'requestReplace']);
        });

    Route::prefix('V1/shop')
        ->name('shop.')
        ->group(function () {
            Route::post('logout', [CommonController::class, 'logout']);
            Route::get('refresh', [CommonController::class, 'refresh']);
            Route::get('contact', [CommonController::class, 'contact']);
            Route::get('terms-conditions', [CommonController::class, 'termsConditions']);
            Route::get('return-refund-policy', [CommonController::class, 'returnRefund']);
            Route::post('delete-account', [CommonController::class, 'deleteAccount']);

            Route::get('shop-account-reason', [ShopCommonController::class, 'userAccountReasons'])
                ->name('shop-delete-reason');

            Route::get('courier', [ShopCommonController::class, 'courier'])
                ->name('courier');

            Route::get('stock-history', [ShopCommonController::class, 'stockHistory'])
                ->name('stock-history');

            Route::get('profile', [ShopCommonController::class, 'profile'])
                ->name('profile');
            Route::post('profile', [ShopCommonController::class, 'profileUpdate'])
                ->name('profile_update');
            Route::get('home', [ShopCommonController::class, 'home'])
                ->name('home');

            Route::get('notification', [ShopCommonController::class, 'notification'])
                ->name('notification');

            Route::get('invoice/{id}', [UserOrderController::class, 'shopInvoice'])
                ->name('invoice');

            Route::get('bank-details', [ShopCommonController::class, 'bankDetails'])
                ->name('bank-details');

            Route::post('bank-details', [ShopCommonController::class, 'bankDetailsUpdate'])
                ->name('bank-details');

            Route::get('brand-select', [ShopCommonController::class, 'brandSelectBoxList'])
                ->name('brand-select');
            Route::get('category-select', [ShopCommonController::class, 'categorySelectBoxList'])
                ->name('category-select');
            Route::get('sub-category-select/{id}', [ShopCommonController::class, 'subCategorySelectBoxList'])
                ->name('category-select');

            Route::get('payout', [ShopPayoutController::class, 'payout'])
                ->name('payout');
            Route::get('payout/{id}', [ShopPayoutController::class, 'payoutDetails'])
                ->name('payout-details');


            Route::get('order-list', [ShopOrderController::class, 'getOrderList'])
                ->name('order_list');

            Route::get('order-details/{id}', [ShopOrderController::class, 'orderDetails'])
                ->name('order_details');
            Route::post('/order-status-change-list', [ShopOrderController::class, 'changeListOrderStatus']);
            Route::post('/order/{id}/status', [ShopOrderController::class, 'changeOrderStatus']);

            Route::post('/order/{order_id}/shop-verify-return/{id}', [ShopOrderController::class, 'shopVerifyReturn']);
            Route::post('/order/{order_id}/shop-verify-replace/{id}', [ShopOrderController::class, 'shopVerifyReplace']);

            Route::get('products', [ShopProductController::class, 'getProductList']);
            Route::post('products', [ShopProductController::class, 'store']);
            Route::get('products/{productId}', [ShopProductController::class, 'productsDetail'])
                ->name('product_detail');
            Route::post('product-update-status/{product}', [ShopProductController::class, 'updateStatus']);

            Route::post('product-price-update/{product}', [ShopProductController::class, 'updatePriceStatus']);
            Route::post('product-stock-update/{product}', [ShopProductController::class, 'updateStock']);

            Route::get('products/{productId}/stock-history', [ShopProductController::class, 'productStockHistory']);

            Route::post('product-description-update/{product}', [ShopProductController::class, 'updateDescriptionRequestSend']);
            Route::post('product-details-update/{product}', [ShopProductController::class, 'updateDetailsRequestSend']);

            Route::post('shop-details-update/{shop}', [ShopProductController::class, 'updateShopDetails']);
        });
});
