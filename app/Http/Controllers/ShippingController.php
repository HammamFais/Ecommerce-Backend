<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShippingController extends Controller
{
    // Fallback ketika RajaOngkir tidak bisa dihubungi
    private array $fallbackRates = [
        'jne' => [
            ['service' => 'REG', 'description' => 'Reguler',              'cost' => [['value' => 18000, 'etd' => '2-3', 'note' => '']]],
            ['service' => 'YES', 'description' => 'Yakin Esok Sampai',   'cost' => [['value' => 35000, 'etd' => '1',   'note' => '']]],
            ['service' => 'OKE', 'description' => 'Ongkos Kirim Ekonomis','cost' => [['value' => 14000, 'etd' => '3-4', 'note' => '']]],
        ],
        'pos' => [
            ['service' => 'Pos Reguler', 'description' => 'Pos Reguler', 'cost' => [['value' => 15000, 'etd' => '3-5', 'note' => '']]],
            ['service' => 'Pos Kilat',   'description' => 'Pos Kilat',   'cost' => [['value' => 28000, 'etd' => '1-2', 'note' => '']]],
        ],
        'tiki' => [
            ['service' => 'REG', 'description' => 'Reguler', 'cost' => [['value' => 16000, 'etd' => '2-3', 'note' => '']]],
            ['service' => 'ONS', 'description' => 'Over Night Service', 'cost' => [['value' => 32000, 'etd' => '1', 'note' => '']]],
        ],
    ];

    public function cost(Request $request): JsonResponse
    {
        $request->validate([
            'origin'      => 'required|string',
            'destination' => 'required|string',
            'weight'      => 'required|integer|min:1',
            'courier'     => 'required|in:jne,pos,tiki',
        ]);

        try {
            $response = Http::timeout(8)->withHeaders([
                'key' => config('services.rajaongkir.api_key'),
            ])->post(config('services.rajaongkir.base_url') . '/cost', [
                'origin'      => $request->origin,
                'destination' => $request->destination,
                'weight'      => $request->weight,
                'courier'     => $request->courier,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $results = $data['rajaongkir']['results'] ?? [];
                if (!empty($results)) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Data ongkir berhasil diambil',
                        'data'    => $results,
                    ]);
                }
            }
        } catch (\Exception $e) {
            // RajaOngkir tidak bisa dihubungi — lanjut ke fallback
        }

        // Kembalikan tarif fallback dengan flag agar frontend bisa bedakan
        $courier = $request->courier;
        $rates   = $this->fallbackRates[$courier] ?? $this->fallbackRates['jne'];

        return response()->json([
            'success'    => true,
            'message'    => 'Data ongkir (estimasi demo)',
            'is_fallback' => true,
            'data'       => [[
                'code'   => strtoupper($courier),
                'name'   => strtoupper($courier),
                'costs'  => $rates,
            ]],
        ]);
    }
}
