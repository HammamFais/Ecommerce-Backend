<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required_without:quantity|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required_without' => 'ID produk wajib diisi',
            'product_id.exists'           => 'Produk tidak ditemukan',
            'quantity.required'           => 'Jumlah wajib diisi',
            'quantity.integer'            => 'Jumlah harus berupa bilangan bulat',
            'quantity.min'                => 'Jumlah minimal 1',
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
