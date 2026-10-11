<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category'    => 'required|string|max:100',
            'image_url'   => 'nullable|url',
            'is_active'   => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama produk wajib diisi',
            'price.required'    => 'Harga produk wajib diisi',
            'price.numeric'     => 'Harga harus berupa angka',
            'price.min'         => 'Harga tidak boleh negatif',
            'stock.required'    => 'Stok produk wajib diisi',
            'stock.integer'     => 'Stok harus berupa bilangan bulat',
            'stock.min'         => 'Stok tidak boleh negatif',
            'category.required' => 'Kategori produk wajib diisi',
            'image_url.url'     => 'Format URL gambar tidak valid',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
