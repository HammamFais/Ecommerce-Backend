<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::with(['seller:id,name', 'items.product'])
            ->where('buyer_id', auth('api')->id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar order berhasil diambil',
            'data'    => $orders,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::with(['buyer:id,name,email', 'seller:id,name', 'items.product', 'payment'])
            ->where('id', $id)
            ->where('buyer_id', auth('api')->id())
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail order berhasil diambil',
            'data'    => $order,
        ]);
    }

    public function store(OrderRequest $request): JsonResponse
    {
        $buyerId = auth('api')->id();

        $cartItems = Cart::with('product')
            ->where('buyer_id', $buyerId)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong, tidak bisa membuat order',
            ], 400);
        }

        // Group cart items by seller
        $itemsBySeller = $cartItems->groupBy(fn($item) => $item->product->seller_id);

        DB::beginTransaction();
        try {
            $orders = [];

            foreach ($itemsBySeller as $sellerId => $items) {
                $subtotal = $items->sum(fn($item) => $item->product->price * $item->quantity);
                $shippingCost = $request->shipping_cost ?? 0;
                $totalPrice = $subtotal + $shippingCost;

                $order = Order::create([
                    'buyer_id'         => $buyerId,
                    'seller_id'        => $sellerId,
                    'total_price'      => $totalPrice,
                    'status'           => 'pending',
                    'shipping_address' => $request->shipping_address,
                    'shipping_city'    => $request->shipping_city,
                    'shipping_cost'    => $shippingCost,
                    'courier'          => $request->courier,
                ]);

                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'price'      => $item->product->price,
                    ]);

                    // Kurangi stok
                    $item->product->decrement('stock', $item->quantity);
                }

                $orders[] = $order->load('items.product');
            }

            // Kosongkan keranjang
            Cart::where('buyer_id', $buyerId)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'data'    => $orders,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat order: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sellerIndex(): JsonResponse
    {
        $orders = Order::with(['buyer:id,name,email', 'items.product'])
            ->where('seller_id', auth('api')->id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar order masuk berhasil diambil',
            'data'    => $orders,
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = Order::where('id', $id)
            ->where('seller_id', auth('api')->id())
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status order berhasil diperbarui',
            'data'    => $order,
        ]);
    }
}
