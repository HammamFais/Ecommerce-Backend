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
        // RajaOngkir V2 domestic-destination adalah autocomplete per keyword,
        // bukan endpoint list kota — gunakan daftar kota besar statis.
        return response()->json([
            'success' => true,
            'data'    => [
                ['city_id' => '17',  'city_name' => 'Aceh Besar',       'province' => 'Aceh'],
                ['city_id' => '20',  'city_name' => 'Aceh Selatan',     'province' => 'Aceh'],
                ['city_id' => '22',  'city_name' => 'Aceh Utara',       'province' => 'Aceh'],
                ['city_id' => '39',  'city_name' => 'Bandung',          'province' => 'Jawa Barat'],
                ['city_id' => '40',  'city_name' => 'Bandung Barat',    'province' => 'Jawa Barat'],
                ['city_id' => '42',  'city_name' => 'Bangkalan',        'province' => 'Jawa Timur'],
                ['city_id' => '52',  'city_name' => 'Batam',            'province' => 'Kepulauan Riau'],
                ['city_id' => '55',  'city_name' => 'Batu',             'province' => 'Jawa Timur'],
                ['city_id' => '57',  'city_name' => 'Bekasi',           'province' => 'Jawa Barat'],
                ['city_id' => '80',  'city_name' => 'Denpasar',         'province' => 'Bali'],
                ['city_id' => '82',  'city_name' => 'Depok',            'province' => 'Jawa Barat'],
                ['city_id' => '99',  'city_name' => 'Gresik',           'province' => 'Jawa Timur'],
                ['city_id' => '151', 'city_name' => 'Jakarta Barat',    'province' => 'DKI Jakarta'],
                ['city_id' => '152', 'city_name' => 'Jakarta Pusat',    'province' => 'DKI Jakarta'],
                ['city_id' => '153', 'city_name' => 'Jakarta Selatan',  'province' => 'DKI Jakarta'],
                ['city_id' => '154', 'city_name' => 'Jakarta Timur',    'province' => 'DKI Jakarta'],
                ['city_id' => '155', 'city_name' => 'Jakarta Utara',    'province' => 'DKI Jakarta'],
                ['city_id' => '158', 'city_name' => 'Jember',           'province' => 'Jawa Timur'],
                ['city_id' => '161', 'city_name' => 'Jombang',          'province' => 'Jawa Timur'],
                ['city_id' => '114', 'city_name' => 'Makassar',         'province' => 'Sulawesi Selatan'],
                ['city_id' => '115', 'city_name' => 'Madiun',           'province' => 'Jawa Timur'],
                ['city_id' => '116', 'city_name' => 'Magelang',         'province' => 'Jawa Tengah'],
                ['city_id' => '171', 'city_name' => 'Malang',           'province' => 'Jawa Timur'],
                ['city_id' => '174', 'city_name' => 'Manado',           'province' => 'Sulawesi Utara'],
                ['city_id' => '177', 'city_name' => 'Mataram',          'province' => 'Nusa Tenggara Barat'],
                ['city_id' => '182', 'city_name' => 'Medan',            'province' => 'Sumatera Utara'],
                ['city_id' => '191', 'city_name' => 'Mojokerto',        'province' => 'Jawa Timur'],
                ['city_id' => '197', 'city_name' => 'Nganjuk',          'province' => 'Jawa Timur'],
                ['city_id' => '199', 'city_name' => 'Ngawi',            'province' => 'Jawa Timur'],
                ['city_id' => '203', 'city_name' => 'Pacitan',          'province' => 'Jawa Timur'],
                ['city_id' => '207', 'city_name' => 'Padang',           'province' => 'Sumatera Barat'],
                ['city_id' => '209', 'city_name' => 'Pamekasan',        'province' => 'Jawa Timur'],
                ['city_id' => '211', 'city_name' => 'Pasuruan',         'province' => 'Jawa Timur'],
                ['city_id' => '214', 'city_name' => 'Pekanbaru',        'province' => 'Riau'],
                ['city_id' => '218', 'city_name' => 'Ponorogo',         'province' => 'Jawa Timur'],
                ['city_id' => '220', 'city_name' => 'Pontianak',        'province' => 'Kalimantan Barat'],
                ['city_id' => '227', 'city_name' => 'Probolinggo',      'province' => 'Jawa Timur'],
                ['city_id' => '244', 'city_name' => 'Samarinda',        'province' => 'Kalimantan Timur'],
                ['city_id' => '262', 'city_name' => 'Sampang',          'province' => 'Jawa Timur'],
                ['city_id' => '264', 'city_name' => 'Semarang',         'province' => 'Jawa Tengah'],
                ['city_id' => '265', 'city_name' => 'Sidoarjo',         'province' => 'Jawa Timur'],
                ['city_id' => '271', 'city_name' => 'Situbondo',        'province' => 'Jawa Timur'],
                ['city_id' => '278', 'city_name' => 'Solo',             'province' => 'Jawa Tengah'],
                ['city_id' => '288', 'city_name' => 'Sumenep',          'province' => 'Jawa Timur'],
                ['city_id' => '444', 'city_name' => 'Surabaya',         'province' => 'Jawa Timur'],
                ['city_id' => '296', 'city_name' => 'Tangerang',        'province' => 'Banten'],
                ['city_id' => '297', 'city_name' => 'Tangerang Selatan','province' => 'Banten'],
                ['city_id' => '301', 'city_name' => 'Tasikmalaya',      'province' => 'Jawa Barat'],
                ['city_id' => '308', 'city_name' => 'Trenggalek',       'province' => 'Jawa Timur'],
                ['city_id' => '309', 'city_name' => 'Tuban',            'province' => 'Jawa Timur'],
                ['city_id' => '310', 'city_name' => 'Tulungagung',      'province' => 'Jawa Timur'],
                ['city_id' => '376', 'city_name' => 'Yogyakarta',       'province' => 'DI Yogyakarta'],
            ],
        ]);
    }
}
