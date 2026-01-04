<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Country;
use App\Models\Category;
use App\Models\Brand;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seed Countries
        $countries = ['Vietnam', 'USA', 'Japan', 'Korea', 'China'];
        foreach ($countries as $country) {
            Country::firstOrCreate(['name' => $country]);
        }
        echo "✅ Countries seeded.\n";

        // 2. Seed Categories
        $categories = ['Bếp điện', 'Gia dụng Nhật', 'Máy Hút Mùi', 'Máy xay & Máy ép', 'Lò vi sóng'];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat]);
        }
        echo "✅ Categories seeded.\n";

        // 3. Seed Brands
        $brands = ['Gertech', 'ROLER', 'Panasonic', 'Bosch', 'YAMATO', 'Chefs', 'Arber', 'Canzy'];
        foreach ($brands as $brand) {
            Brand::firstOrCreate(['name' => $brand]);
        }
        echo "✅ Brands seeded.\n";

        // 4. Seed Admin User
        $admin = User::where('email', 'admin@gmail.com')->first();
        if (!$admin) {
            User::create([
                'name' => 'Admin Shop',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('123456'),
                'phone' => '0123456789',
                'address' => 'Hanoi, Vietnam',
                'avatar' => json_encode(['avatars/default-admin.png']),
                'level' => 0, // Admin
                'is_active' => 1,
                'id_country' => 1
            ]);
            echo "✅ Admin user created (admin@gmail.com / 123456).\n";
        }

        // 5. Seed Test Member User
        $member = User::where('email', 'member@gmail.com')->first();
        if (!$member) {
            User::create([
                'name' => 'Test Member',
                'email' => 'member@gmail.com',
                'password' => Hash::make('123456'),
                'phone' => '0987654321',
                'address' => 'HCM City, Vietnam',
                'avatar' => json_encode(['avatars/default-user.png']),
                'level' => 1, // Member
                'is_active' => 1,
                'id_country' => 1
            ]);
            echo "✅ Member user created (member@gmail.com / 123456).\n";
        }

        // 6. Seed Products
        if (DB::table('products')->count() === 0) {
            $products = [
                [
                    'id_category' => 1,
                    'id_brand' => 1,
                    'name' => 'Samsung Refrigerator RT20',
                    'image' => json_encode(['productImages/sample_fridge.png']),
                    'price' => 7500000,
                    'status' => 1,
                    'sale' => 10,
                    'detail' => 'High quality refrigerator with inverter technology.',
                    'quantity' => 20,
                    'quantity_sold' => 5
                ],
                [
                    'id_category' => 1,
                    'id_brand' => 2,
                    'name' => 'LG Microwave Oven',
                    'image' => json_encode(['productImages/sample_microwave.png']),
                    'price' => 3200000,
                    'status' => 1,
                    'sale' => 0,
                    'detail' => 'Quick and efficient cooking with multiple modes.',
                    'quantity' => 15,
                    'quantity_sold' => 2
                ],
                [
                    'id_category' => 4,
                    'id_brand' => 1,
                    'name' => 'Samsung 4K Smart TV 55"',
                    'image' => json_encode(['productImages/sample_tv.png']),
                    'price' => 12000000,
                    'status' => 1,
                    'sale' => 15,
                    'detail' => 'Ultra HD Smart TV with stunning colors.',
                    'quantity' => 10,
                    'quantity_sold' => 3
                ],
                [
                    'id_category' => 4,
                    'id_brand' => 7,
                    'name' => 'MacBook Air M2',
                    'image' => json_encode(['productImages/sample_macbook.png']),
                    'price' => 28000000,
                    'status' => 1,
                    'sale' => 5,
                    'detail' => 'Supercharged by M2 chip, thin and light.',
                    'quantity' => 8,
                    'quantity_sold' => 1
                ],
                [
                    'id_category' => 2,
                    'id_brand' => 8,
                    'name' => 'Sony Bluetooth Speaker SRS-XB13',
                    'image' => json_encode(['productImages/sample_speaker.png']),
                    'price' => 1290000,
                    'status' => 1,
                    'sale' => 20,
                    'detail' => 'Compact and portable with deep bass.',
                    'quantity' => 50,
                    'quantity_sold' => 12
                ],
                [
                    'id_category' => 1,
                    'id_brand' => 6,
                    'name' => 'Xiaomi Mi Smart Air Fryer',
                    'image' => json_encode(['productImages/sample_fryer.png']),
                    'price' => 2100000,
                    'status' => 1,
                    'sale' => 0,
                    'detail' => 'Healthy oil-free cooking with App control.',
                    'quantity' => 30,
                    'quantity_sold' => 10
                ]
            ];

            foreach ($products as $p) {
                DB::table('products')->insert($p);
            }
            echo "✅ Products seeded.\n";
        }
    }
}
