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

// TEMPORARY — test koneksi RajaOngkir dari Railway, hapus setelah selesai
Route::get('test-rajaongkir', function () {
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders(['key' => config('services.rajaongkir.api_key')])
            ->post('https://api.rajaongkir.com/starter/cost', [
                'origin'      => '444',
                'destination' => '154',
                'weight'      => '1000',
                'courier'     => 'jne',
            ]);
        return response()->json([
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
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
