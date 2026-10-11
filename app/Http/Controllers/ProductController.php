<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('seller:id,name,city,city_id')
            ->where('is_active', true);

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->search) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $products = $query->latest()->paginate(12);

        return response()->json([
            'success' => true,
            'message' => 'Daftar produk berhasil diambil',
            'data'    => $products,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with('seller:id,name,city,city_id,province')->find($id);

        if (!$product || !$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil',
            'data'    => $product,
        ]);
    }

    public function sellerIndex(): JsonResponse
    {
        $products = Product::where('seller_id', auth('api')->id())
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Daftar produk milik Anda berhasil diambil',
            'data'    => $products,
        ]);
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create([
            'seller_id'   => auth('api')->id(),
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'category'    => $request->category,
            'image_url'   => $request->image_url,
            'is_active'   => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data'    => $product,
        ], 201);
    }

    public function update(ProductRequest $request, int $id): JsonResponse
    {
        $product = Product::where('id', $id)
            ->where('seller_id', auth('api')->id())
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan atau bukan milik Anda',
            ], 404);
        }

        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data'    => $product,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::where('id', $id)
            ->where('seller_id', auth('api')->id())
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan atau bukan milik Anda',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus',
        ]);
    }
}
