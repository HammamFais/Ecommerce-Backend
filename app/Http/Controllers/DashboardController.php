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
        $paidStatuses = ['dibayar', 'diproses', 'dikirim', 'selesai'];

        $totalSales = Order::where('seller_id', $sellerId)
            ->whereIn('status', $paidStatuses)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_price');

        $lastMonthSales = Order::where('seller_id', $sellerId)
            ->whereIn('status', $paidStatuses)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total_price');

        $totalOrders = Order::where('seller_id', $sellerId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthOrders = Order::where('seller_id', $sellerId)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $pendingOrders = Order::where('seller_id', $sellerId)
            ->where('status', 'pending')
            ->count();

        $activeProducts = Product::where('seller_id', $sellerId)
            ->where('is_active', true)
            ->count();

        $lowStockProducts = Product::where('seller_id', $sellerId)
            ->where('stock', '<=', 5)
            ->where('is_active', true)
            ->get(['id', 'name', 'stock', 'category']);

        $revenueChart = Order::where('seller_id', $sellerId)
            ->whereIn('status', $paidStatuses)
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_price) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $orderStatusCounts = Order::where('seller_id', $sellerId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $recentOrders = Order::with(['buyer:id,name', 'items.product'])
            ->where('seller_id', $sellerId)
            ->latest()
            ->take(5)
            ->get();

        $salesGrowth = $lastMonthSales > 0
            ? round((($totalSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : ($totalSales > 0 ? 100 : 0);

        $ordersGrowth = $lastMonthOrders > 0
            ? round((($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : ($totalOrders > 0 ? 100 : 0);

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil',
            'data'    => [
                'total_sales'        => $totalSales,
                'sales_growth'       => $salesGrowth,
                'total_orders'       => $totalOrders,
                'orders_growth'      => $ordersGrowth,
                'pending_orders'     => $pendingOrders,
                'active_products'    => $activeProducts,
                'low_stock_products' => $lowStockProducts,
                'revenue_chart'      => $revenueChart,
                'order_status_counts' => $orderStatusCounts,
                'recent_orders'      => $recentOrders,
            ],
        ]);
    }
}
