<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => 'required|string',
            'shipping_city'    => 'required|string|max:100',
            'shipping_cost'    => 'nullable|numeric|min:0',
            'courier'          => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address.required' => 'Alamat pengiriman wajib diisi',
            'shipping_city.required'    => 'Kota pengiriman wajib diisi',
            'shipping_cost.numeric'     => 'Ongkir harus berupa angka',
            'shipping_cost.min'         => 'Ongkir tidak boleh negatif',
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
