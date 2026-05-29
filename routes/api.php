<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShippingController;
use Illuminate\Support\Facades\Route;

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::put('profile', [AuthController::class, 'updateProfile'])->middleware('auth:api');
});

// Product routes (public)
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);

// Midtrans webhook (public, no auth)
Route::post('payment/notification', [PaymentController::class, 'notification']);

// Shipping cities (public, dipakai saat load profile page)
Route::get('shipping/cities', [ShippingController::class, 'cities']);

// TEMPORARY — isolasi masalah API key RajaOngkir, hapus setelah selesai
Route::get('test-rajaongkir', function () {
    $url    = 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost';
    $params = ['origin' => '444', 'destination' => '154', 'weight' => '1000', 'courier' => 'jne'];

    $keyFromConfig   = config('services.rajaongkir.api_key');
    $keyHardcoded    = 'nq8cNmaQ0897a0aca12a5904LlwzkPpt';

    $hit = function (string $key) use ($url, $params): array {
        try {
            $res = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders(['key' => $key])
                ->asForm()
                ->post($url, $params);
            return ['status' => $res->status(), 'body' => $res->json()];
        } catch (\Exception $e) {
            return ['status' => 'exception', 'error' => $e->getMessage()];
        }
    };

    return response()->json([
        'test1_config' => [
            'key_prefix' => substr($keyFromConfig, 0, 5),
            'result'     => $hit($keyFromConfig),
        ],
        'test2_hardcoded' => [
            'key_prefix' => substr($keyHardcoded, 0, 5),
            'result'     => $hit($keyHardcoded),
        ],
    ]);
});

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Cart (buyer)
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::put('cart/{id}', [CartController::class, 'update']);
    Route::delete('cart/{id}', [CartController::class, 'destroy']);
    Route::delete('cart', [CartController::class, 'clear']);

    // Orders (buyer)
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::put('orders/{id}/cancel', [OrderController::class, 'cancelOrder']);

    // Payment
    Route::post('payment/{order_id}', [PaymentController::class, 'create']);
    Route::get('payment/status/{order_id}', [PaymentController::class, 'status']);

    // Shipping
    Route::get('shipping/cost', [ShippingController::class, 'cost']);

    // Seller routes
    Route::middleware('role:seller,admin')->prefix('seller')->group(function () {
        Route::get('products', [ProductController::class, 'sellerIndex']);
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);

        Route::get('orders', [OrderController::class, 'sellerIndex']);
        Route::put('orders/{id}', [OrderController::class, 'updateStatus']);
    });

    // Dashboard seller
    Route::get('dashboard/seller', [DashboardController::class, 'seller'])
        ->middleware('role:seller,admin');
});
