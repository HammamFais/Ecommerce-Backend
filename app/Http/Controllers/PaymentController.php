<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function create(int $orderId): JsonResponse
    {
        $order = Order::with(['buyer', 'items.product'])
            ->where('id', $orderId)
            ->where('buyer_id', auth('api')->id())
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order ini tidak dalam status pending',
            ], 400);
        }

        $midtransOrderId = 'PL-' . $order->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id'     => $midtransOrderId,
                'gross_amount' => (int) $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $order->buyer->name,
                'email'      => $order->buyer->email,
                'phone'      => $order->buyer->phone ?? '',
            ],
            'item_details' => $order->items->map(fn($item) => [
                'id'       => $item->product_id,
                'price'    => (int) $item->price,
                'quantity' => $item->quantity,
                'name'     => $item->product->name,
            ])->toArray(),
        ];

        if ($order->shipping_cost > 0) {
            $params['item_details'][] = [
                'id'       => 'SHIPPING',
                'price'    => (int) $order->shipping_cost,
                'quantity' => 1,
                'name'     => 'Ongkos Kirim (' . ($order->courier ?? '') . ')',
            ];
        }

        $snapToken = Snap::getSnapToken($params);

        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'midtrans_order_id' => $midtransOrderId,
                'midtrans_token'    => $snapToken,
                'status'            => 'pending',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Token pembayaran berhasil dibuat',
            'data'    => [
                'snap_token'        => $snapToken,
                'midtrans_order_id' => $midtransOrderId,
                'order'             => $order,
            ],
        ]);
    }

    public function notification(Request $request): JsonResponse
    {
        $notification = new Notification();

        $orderId = $notification->order_id;
        $status = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status;

        $payment = Payment::where('midtrans_order_id', $orderId)->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if ($status === 'capture' && $fraudStatus === 'accept') {
            $payment->update(['status' => 'success', 'paid_at' => now(), 'payment_method' => $notification->payment_type]);
            $payment->order->update(['status' => 'paid']);
        } elseif ($status === 'settlement') {
            $payment->update(['status' => 'success', 'paid_at' => now(), 'payment_method' => $notification->payment_type]);
            $payment->order->update(['status' => 'paid']);
        } elseif (in_array($status, ['deny', 'expire', 'cancel'])) {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['success' => true, 'message' => 'OK']);
    }

    public function status(int $orderId): JsonResponse
    {
        $order = Order::where('id', $orderId)
            ->where('buyer_id', auth('api')->id())
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran belum ada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran berhasil diambil',
            'data'    => $payment,
        ]);
    }
}
