<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        $cart = Cart::with('product')
            ->where('buyer_id', auth('api')->id())
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diambil',
            'data'    => $cart,
        ]);
    }

    public function store(CartRequest $request): JsonResponse
    {
        $product = Product::find($request->product_id);

        if (!$product || !$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan atau tidak aktif',
            ], 404);
        }

        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Stok produk tidak mencukupi',
            ], 400);
        }

        $existing = Cart::where('buyer_id', auth('api')->id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            $existing->update(['quantity' => $existing->quantity + $request->quantity]);
            $cart = $existing;
        } else {
            $cart = Cart::create([
                'buyer_id'   => auth('api')->id(),
                'product_id' => $request->product_id,
                'quantity'   => $request->quantity,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang',
            'data'    => $cart->load('product'),
        ], 201);
    }

    public function update(CartRequest $request, int $id): JsonResponse
    {
        $cart = Cart::where('id', $id)
            ->where('buyer_id', auth('api')->id())
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Item keranjang tidak ditemukan',
            ], 404);
        }

        if ($cart->product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Stok produk tidak mencukupi',
            ], 400);
        }

        $cart->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui',
            'data'    => $cart->load('product'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $cart = Cart::where('id', $id)
            ->where('buyer_id', auth('api')->id())
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Item keranjang tidak ditemukan',
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari keranjang',
        ]);
    }

    public function clear(): JsonResponse
    {
        Cart::where('buyer_id', auth('api')->id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan',
        ]);
    }
}
