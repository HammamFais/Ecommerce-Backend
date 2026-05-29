<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShippingController extends Controller
{
    private const RAJAONGKIR_V2 = 'https://rajaongkir.komerce.id/api/v1';

    // Fallback ketika RajaOngkir tidak bisa dihubungi
    // Format mengikuti V2: cost integer langsung, etd string langsung
    private array $fallbackRates = [
        'jne' => [
            ['service' => 'REG', 'description' => 'Reguler',               'cost' => 18000, 'etd' => '2-3 days'],
            ['service' => 'YES', 'description' => 'Yakin Esok Sampai',    'cost' => 35000, 'etd' => '1 day'],
            ['service' => 'OKE', 'description' => 'Ongkos Kirim Ekonomis', 'cost' => 14000, 'etd' => '3-4 days'],
        ],
        'pos' => [
            ['service' => 'Pos Reguler', 'description' => 'Pos Reguler', 'cost' => 15000, 'etd' => '3-5 days'],
            ['service' => 'Pos Kilat',   'description' => 'Pos Kilat',   'cost' => 28000, 'etd' => '1-2 days'],
        ],
        'tiki' => [
            ['service' => 'REG', 'description' => 'Reguler',           'cost' => 16000, 'etd' => '2-3 days'],
            ['service' => 'ONS', 'description' => 'Over Night Service', 'cost' => 32000, 'etd' => '1 day'],
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
            $response = Http::timeout(8)
                ->withHeaders(['key' => config('services.rajaongkir.api_key')])
                ->asForm()
                ->post(self::RAJAONGKIR_V2 . '/calculate/domestic-cost', [
                    'origin'      => $request->origin,
                    'destination' => $request->destination,
                    'weight'      => $request->weight,
                    'courier'     => $request->courier,
                ]);

            if ($response->successful()) {
                $data    = $response->json();
                $results = $data['data'] ?? [];
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

        $courier = $request->courier;
        $rates   = $this->fallbackRates[$courier] ?? $this->fallbackRates['jne'];

        return response()->json([
            'success'     => true,
            'message'     => 'Data ongkir (estimasi demo)',
            'is_fallback' => true,
            'data'        => $rates,
        ]);
    }

    public function cities(): JsonResponse
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(['key' => config('services.rajaongkir.api_key')])
                ->get(self::RAJAONGKIR_V2 . '/destination/domestic-destination', [
                    'search' => '',
                ]);

            if ($response->successful()) {
                $data    = $response->json();
                $results = $data['data'] ?? [];
                if (!empty($results)) {
                    // Map ke format lama agar frontend tidak perlu diubah
                    $cities = array_map(fn($item) => [
                        'city_id'   => $item['id'],
                        'city_name' => $item['city_name'],
                        'province'  => $item['province_name'],
                    ], $results);

                    return response()->json([
                        'success' => true,
                        'data'    => $cities,
                    ]);
                }
            }
        } catch (\Exception $e) {}

        // Fallback: kota-kota besar
        return response()->json([
            'success'     => true,
            'is_fallback' => true,
            'data'        => [
                ['city_id' => '39',  'city_name' => 'Bandung',         'province' => 'Jawa Barat'],
                ['city_id' => '80',  'city_name' => 'Denpasar',        'province' => 'Bali'],
                ['city_id' => '114', 'city_name' => 'Makassar',        'province' => 'Sulawesi Selatan'],
                ['city_id' => '152', 'city_name' => 'Jakarta Pusat',   'province' => 'DKI Jakarta'],
                ['city_id' => '151', 'city_name' => 'Jakarta Barat',   'province' => 'DKI Jakarta'],
                ['city_id' => '153', 'city_name' => 'Jakarta Selatan', 'province' => 'DKI Jakarta'],
                ['city_id' => '154', 'city_name' => 'Jakarta Timur',   'province' => 'DKI Jakarta'],
                ['city_id' => '155', 'city_name' => 'Jakarta Utara',   'province' => 'DKI Jakarta'],
                ['city_id' => '171', 'city_name' => 'Malang',          'province' => 'Jawa Timur'],
                ['city_id' => '244', 'city_name' => 'Medan',           'province' => 'Sumatera Utara'],
                ['city_id' => '263', 'city_name' => 'Palembang',       'province' => 'Sumatera Selatan'],
                ['city_id' => '288', 'city_name' => 'Pontianak',       'province' => 'Kalimantan Barat'],
                ['city_id' => '371', 'city_name' => 'Semarang',        'province' => 'Jawa Tengah'],
                ['city_id' => '399', 'city_name' => 'Solo',            'province' => 'Jawa Tengah'],
                ['city_id' => '444', 'city_name' => 'Surabaya',        'province' => 'Jawa Timur'],
                ['city_id' => '455', 'city_name' => 'Tangerang',       'province' => 'Banten'],
                ['city_id' => '501', 'city_name' => 'Yogyakarta',      'province' => 'DI Yogyakarta'],
            ],
        ]);
    }
}
