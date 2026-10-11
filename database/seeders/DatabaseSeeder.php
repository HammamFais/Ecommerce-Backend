<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::firstOrCreate(
            ['email' => 'seller@demo.com'],
            [
                'name'     => 'Toko Berkah UMKM',
                'password' => Hash::make('password123'),
                'role'     => 'seller',
                'phone'    => '081234567890',
                'address'  => 'Jl. Raya Darmo No. 12',
                'city'     => 'Surabaya',
                'city_id'  => '444',
                'province' => 'Jawa Timur',
            ]
        );

        // Jika seller sudah ada tapi belum punya city_id, update
        if (!$seller->city_id) {
            $seller->update(['city' => 'Surabaya', 'city_id' => '444']);
        }

        $buyer = User::firstOrCreate(
            ['email' => 'buyer@demo.com'],
            [
                'name'     => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role'     => 'buyer',
                'phone'    => '082345678901',
                'address'  => 'Jl. Pemuda No. 45',
                'city'     => 'Jakarta Pusat',
                'city_id'  => '152',
                'province' => 'DKI Jakarta',
            ]
        );

        if (!$buyer->city_id) {
            $buyer->update(['city' => 'Jakarta Pusat', 'city_id' => '152']);
        }

        $products = [
            [
                'name'        => 'Batik Tulis Motif Parang',
                'description' => 'Batik tulis asli Solo dengan motif parang klasik. Dibuat tangan oleh pengrajin berpengalaman. Cocok untuk acara formal maupun semi-formal.',
                'price'       => 285000,
                'stock'       => 15,
                'category'    => 'fashion',
                'image_url'   => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=400',
                'is_active'   => true,
            ],
            [
                'name'        => 'Keripik Tempe Renyah Original',
                'description' => 'Keripik tempe homemade khas Malang. Renyah, gurih, tanpa pengawet. Tersedia kemasan 250gr dan 500gr. Cocok untuk camilan keluarga.',
                'price'       => 22000,
                'stock'       => 80,
                'category'    => 'makanan',
                'image_url'   => 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=400',
                'is_active'   => true,
            ],
            [
                'name'        => 'Kopi Arabika Gayo Premium 250gr',
                'description' => 'Kopi arabika single origin dari Gayo, Aceh. Roast level medium, aroma fruity dengan sentuhan cokelat. Cocok untuk pour over & french press.',
                'price'       => 75000,
                'stock'       => 40,
                'category'    => 'minuman',
                'image_url'   => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=400',
                'is_active'   => true,
            ],
            [
                'name'        => 'Tas Anyam Rotan Handmade',
                'description' => 'Tas anyam dari rotan asli Kalimantan. Dikerjakan tangan oleh pengrajin lokal. Desain modern, kuat, dan ramah lingkungan. Ukuran 30x25x10 cm.',
                'price'       => 145000,
                'stock'       => 20,
                'category'    => 'kerajinan',
                'image_url'   => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400',
                'is_active'   => true,
            ],
            [
                'name'        => 'Sambal Bajak Bu Tini 250gr',
                'description' => 'Sambal bajak rumahan dengan resep turun-temurun. Bahan alami pilihan, pedas sedang, cocok untuk lauk sehari-hari. Tahan 2 minggu tanpa kulkas.',
                'price'       => 28000,
                'stock'       => 60,
                'category'    => 'makanan',
                'image_url'   => 'https://images.unsplash.com/photo-1574484284002-952d92456975?w=400',
                'is_active'   => true,
            ],
            [
                'name'        => 'Gelang Perak Motif Bali',
                'description' => 'Gelang perak asli dari Celuk, Bali. Dikerjakan oleh pengrajin perak berpengalaman. Motif ukir tradisional Bali. Berat ±15 gram, bisa di-resize.',
                'price'       => 195000,
                'stock'       => 25,
                'category'    => 'aksesoris',
                'image_url'   => 'https://images.unsplash.com/photo-1573408301185-9519f94815c5?w=400',
                'is_active'   => true,
            ],
            [
                'name'        => 'Minyak Kelapa Murni VCO 500ml',
                'description' => 'Virgin Coconut Oil cold-pressed dari kelapa segar Sulawesi. Tanpa pemanas, tanpa bahan kimia. Multifungsi untuk masak, kulit, dan rambut.',
                'price'       => 55000,
                'stock'       => 35,
                'category'    => 'kesehatan',
                'image_url'   => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=400',
                'is_active'   => true,
            ],
            [
                'name'        => 'Kursi Kayu Jati Minimalis',
                'description' => 'Kursi kayu jati solid buatan pengrajin Jepara. Finishing natural oil, desain minimalis modern. Kuat dan tahan lama hingga puluhan tahun.',
                'price'       => 850000,
                'stock'       => 8,
                'category'    => 'furnitur',
                'image_url'   => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400',
                'is_active'   => true,
            ],
            [
                'name'        => 'Teh Herbal Jahe Merah & Sereh',
                'description' => 'Teh herbal racikan tradisional dari jahe merah dan sereh segar. Bantu hangatkan tubuh, tingkatkan imunitas. Isi 20 sachet per box.',
                'price'       => 35000,
                'stock'       => 55,
                'category'    => 'minuman',
                'image_url'   => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400',
                'is_active'   => true,
            ],
            [
                'name'        => 'Lukisan Cat Minyak Pemandangan Sawah',
                'description' => 'Lukisan cat minyak original karya pelukis lokal Yogyakarta. Tema pemandangan sawah pagi hari. Ukuran 60x80 cm, sudah dibingkai kayu jati.',
                'price'       => 450000,
                'stock'       => 5,
                'category'    => 'kerajinan',
                'image_url'   => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=400',
                'is_active'   => true,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name'], 'seller_id' => $seller->id],
                array_merge($product, ['seller_id' => $seller->id])
            );
        }
    }
}
