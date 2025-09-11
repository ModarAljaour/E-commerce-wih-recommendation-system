<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckOutController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SubCategoryController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('admin/login', [AuthController::class, 'login']);

Route::post('admin/register', [AuthController::class, 'register']);


Route::post('login', [AuthController::class, 'CustomerLogin']);
Route::post('register', [AuthController::class, 'CustomerRegister']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::middleware('admin')->prefix('admin')->group(function () {

    // Brands :
    Route::post('/brands/index', [BrandController::class, 'index']);
    Route::post('/brands/create', [BrandController::class, 'store']);
    Route::post('/brands/update/{id}', [BrandController::class, 'update']);
    Route::delete('/brands/destroy/{id}', [BrandController::class, 'destroy']);

    // Product :
    Route::post('/product/index', [ProductController::class, 'index']);
    Route::post('/product/create', [ProductController::class, 'store']);
    Route::post('/product/update/{id}', [ProductController::class, 'update']);
    Route::delete('/product/destroy/{id}', [ProductController::class, 'destroy']);

    // Category :
    Route::post('/category/create', [CategoryController::class, 'store']);
    Route::get('/category/index', [CategoryController::class, 'index']);
    Route::post('/category/update/{id}', [CategoryController::class, 'update']);
    Route::delete('/category/destroy/{id}', [CategoryController::class, 'destroy']);

    // Sub Category :
    Route::get('/subcategory/index', [SubCategoryController::class, 'index']);
    Route::post('/subcategory/create', [SubCategoryController::class, 'store']);
    Route::post('/subcategory/update/{id}', [SubCategoryController::class, 'update']);
    Route::delete('/subcategory/destroy/{id}', [SubCategoryController::class, 'destroy']);
    Route::get('/subcategory/show/{id}', [SubCategoryController::class, 'show']); // add to customer

    // Coupons :
    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/coupons/create', [CouponController::class, 'store']);
    Route::get('/coupons/{id}', [CouponController::class, 'show']);
    Route::put('/coupons/{id}/update', [CouponController::class, 'update']);
    Route::delete('/coupons/{id}/delete', [CouponController::class, 'destroy']);

    // Change Status Order :
    Route::middleware('auth:sanctum')
        ->post('/orders/status/{order}', [CheckOutController::class, 'updateStatus']);



    // Invoice :
    Route::post('/invoices/create', [InvoiceController::class, 'store']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::put('/invoices/{invoice}/edit', [InvoiceController::class, 'update']);
});



// customer api route :
Route::middleware(['auth:sanctum', 'customer'])->prefix('customer')->group(function () {

    // Cart :
    Route::post('/cart/index', [CartController::class, 'index']);
    Route::post('/cart/create', [CartController::class, 'store']);
    Route::post('/cart/update/', [CartController::class, 'update']);
    Route::post('/cart/destroy', [CartController::class, 'destroy']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);

    // Wish List :
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/add', [WishlistController::class, 'store']);
    Route::post('/wishlist/remove', [WishlistController::class, 'destroy']);
    Route::post('/wishlist/toCart', [WishlistController::class, 'moveToCart']);
    Route::post('/wishlist/count', [WishlistController::class, 'count']);

    // Checkout :
    Route::get('/checkout/index', [CheckOutController::class, 'index']);
    Route::get('/checkout/cartInfo', [CheckOutController::class, 'CartInfo']);
    Route::post('/checkout/create', [CheckOutController::class, 'store']);
    Route::get('/checkout/show/{id}', [CheckOutController::class, 'show']);
    Route::post('/checkout/apply-coupon', [CheckoutController::class, 'applyCoupon']);

    //

    // Invoice for customer :
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
});

Route::post('/product/index', [ProductController::class, 'index']);
