<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function seller(): JsonResponse
    {
        $sellerId = auth('api')->id();

        $totalSales = Order::where('seller_id', $sellerId)
            ->where('status', 'done')
            ->sum('total_price');

        $pendingOrders = Order::where('seller_id', $sellerId)
            ->where('status', 'pending')
            ->count();

        $totalOrders = Order::where('seller_id', $sellerId)->count();

        $lowStockProducts = Product::where('seller_id', $sellerId)
            ->where('stock', '<=', 5)
            ->where('is_active', true)
            ->get(['id', 'name', 'stock', 'category']);

        $recentOrders = Order::with(['buyer:id,name', 'items.product'])
            ->where('seller_id', $sellerId)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil',
            'data'    => [
                'total_sales'       => $totalSales,
                'pending_orders'    => $pendingOrders,
                'total_orders'      => $totalOrders,
                'low_stock_products' => $lowStockProducts,
                'recent_orders'     => $recentOrders,
            ],
        ]);
    }
}
