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
        $brands = ['Bosch', 'Chefs', 'Arber', 'Canzy', 'YAMATO', 'Gertech', 'ROLER', 'Panasonic', 'Kocher'];
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
                'level' => 1, // Admin
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
                'level' => 0, // Member
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
                    'name' => 'Bếp ba từ Bosch PUC631BB2E',
                    'image' => json_encode(['productImages/bep-ba-tu-bosch-puc631bb2e.jpg']),
                    'price' => 13500000,
                    'status' => 1,
                    'sale' => 10,
                    'detail' => '“Con cưng” của hãng Bosch, đỉnh cao về công nghệ, chất lượng.',
                    'quantity' => 71,
                    'quantity_sold' => 5
                ],
                [
                    'id_category' => 1,
                    'id_brand' => 1,
                    'name' => 'Bếp ba từ Bosch PUJ631BB2E',
                    'image' => json_encode(['productImages/bep-ba-tu-bosch-puj631bb2e.png']),
                    'price' => 14600000,
                    'status' => 1,
                    'sale' => 5,
                    'detail' => 'Thiết kế cao cấp, vùng nấu rộng cho nồi cỡ lớn.',
                    'quantity' => 15,
                    'quantity_sold' => 2
                ],
                [
                    'id_category' => 1,
                    'id_brand' => 2,
                    'name' => 'Bếp ba từ Chefs EH-IH566',
                    'image' => json_encode(['productImages/bep-ba-tu-chefs-eh-ih566.png']),
                    'price' => 22000000,
                    'status' => 1,
                    'sale' => 15,
                    'detail' => 'Bếp từ ba EH-IH566 ứng dụng công nghệ Inverter thông minh vượt trội.',
                    'quantity' => 29,
                    'quantity_sold' => 3
                ],
                [
                    'id_category' => 1,
                    'id_brand' => 3,
                    'name' => 'Bếp điện từ Arber AB 380',
                    'image' => json_encode(['productImages/bep-dien-tu-arber-ab-380.jpg']),
                    'price' => 8613000,
                    'status' => 1,
                    'sale' => 5,
                    'detail' => 'Chế độ hẹn giờ độc lập cho từng bếp, báo động bằng âm thanh.',
                    'quantity' => 58,
                    'quantity_sold' => 1
                ],
                [
                    'id_category' => 1,
                    'id_brand' => 4,
                    'name' => 'Bếp điện từ Canzy CZ-3002GS',
                    'image' => json_encode(['productImages/bep-dien-tu-canzy-cz-3002gs.jpg']),
                    'price' => 3990000,
                    'status' => 1,
                    'sale' => 20,
                    'detail' => 'Điều khiển độc lập cho từng vùng nấu.',
                    'quantity' => 50,
                    'quantity_sold' => 12
                ],
                [
                    'id_category' => 1,
                    'id_brand' => 6,
                    'name' => 'Bếp điện từ đôi YAMATO YMT-368',
                    'image' => json_encode(['productImages/bep-dien-tu-doi-yamato-ymt-368.jpg']),
                    'price' => 23500000,
                    'status' => 1,
                    'sale' => 7,
                    'detail' => 'Bảng điều khiển cảm ứng chạm-trượt với màn hình hiển thị LCD.',
                    'quantity' => 34,
                    'quantity_sold' => 10
                ],
                [
                    'id_category' => 3,
                    'id_brand' => 4,
                    'name' => 'Hút mùi Canzy CZ 3470',
                    'image' => json_encode(['productImages/hut-mui-canzy-cz-3470.jpg']),
                    'price' => 4250000,
                    'status' => 1,
                    'sale' => 18,
                    'detail' => 'Phím điều khiển: nút nhấn cơ điện tử.',
                    'quantity' => 50,
                    'quantity_sold' => 12
                ],
                [
                    'id_category' => 3,
                    'id_brand' => 9,
                    'name' => 'Hút mùi kính cong Kocher K8370',
                    'image' => json_encode(['productImages/hut-mui-kinh-cong-kocher-k8370.jpg']),
                    'price' => 4125000,
                    'status' => 1,
                    'sale' => 15,
                    'detail' => 'Kiểu dáng: Toa kính cong gắn tường.',
                    'quantity' => 50,
                    'quantity_sold' => 12
                ],
                [
                    'id_category' => 3,
                    'id_brand' => 5,
                    'name' => 'Hút mùi YAMATO YT-269H-S',
                    'image' => json_encode(['productImages/hut-mui-yamato-yt-269h-s.jpg']),
                    'price' => 11500000,
                    'status' => 1,
                    'sale' => 13,
                    'detail' => 'Bộ lọc: 02 tấm lọc nhôm + 02 tấm lọc than hoạt tính khử mùi.',
                    'quantity' => 70,
                    'quantity_sold' => 12
                ],
                [
                    'id_category' => 5,
                    'id_brand' => 6,
                    'name' => 'Lò nướng điện thông minh GERTECH GT-688',
                    'image' => json_encode(['productImages/lo-nuong-dien-thong-minh-gertech-gt-688.jpg']),
                    'price' => 13000000,
                    'status' => 1,
                    'sale' => 8,
                    'detail' => '4 tính năng: Hẹn giờ, Khóa trẻ em, Báo thức, Đặt trước, Chế độ tiết kiệm năng lượng.',
                    'quantity' => 58,
                    'quantity_sold' => 1
                ],
                [
                    'id_category' => 4,
                    'id_brand' => 6,
                    'name' => 'Máy ép chậm GERTECH GT-J206 cao cấp giữ nguyên vẹn dưỡng chất',
                    'image' => json_encode(['productImages/may-ep-cham-gertech-gt-j206-cao-cap-giu-nguyen-ven-duong-chat.png']),
                    'price' => 3950000,
                    'status' => 1,
                    'sale' => 14,
                    'detail' => 'Sử dụng công nghệ trục vít làm bằng chất liệu nhựa tritan không chứa BPA.',
                    'quantity' => 50,
                    'quantity_sold' => 12
                ],
                [
                    'id_category' => 2,
                    'id_brand' => 8,
                    'name' => 'Bếp từ All Metal Panasonic KZ-W573S',
                    'image' => json_encode(['productImages/bep-tu-all-metal-panasonic-kz-w573s.jpg']),
                    'price' => 28500000,
                    'status' => 1,
                    'sale' => 7,
                    'detail' => 'Công nghệ Econani tiết kiệm điện năng tối đa. Chi phí cho năng lượng cho nấu ăn sẽ giảm đi nhiều so với dùng bếp gas.',
                    'quantity' => 34,
                    'quantity_sold' => 10
                ],
                [
                    'id_category' => 3,
                    'id_brand' => 4,
                    'name' => 'Bếp từ Kocher DI 6900A',
                    'image' => json_encode(['productImages/bep-tu-kocher-dl-6900a.jpg']),
                    'price' => 5900000,
                    'status' => 1,
                    'sale' => 16,
                    'detail' => 'Thương hiệu Việt – Chất lượng Đức – Chất đến từng linh kiện.',
                    'quantity' => 51,
                    'quantity_sold' => 12
                ],
                [
                    'id_category' => 1,
                    'id_brand' => 6,
                    'name' => 'Bếp Điện Từ Đôi GERTECH GT-5202B',
                    'image' => json_encode(['productImages/bep-dien-tu-doi-gertech-gt-5202b.png']),
                    'price' => 21000000,
                    'status' => 1,
                    'sale' => 13,
                    'detail' => 'Chức năng Hâm nóng; Chức năng Tạm dừng; Chức năng Hẹn giờ tối đa 90p.',
                    'quantity' => 50,
                    'quantity_sold' => 12
                ],
                [
                    'id_category' => 1,
                    'id_brand' => 5,
                    'name' => 'Bếp Điện Bosch PKF645E14E',
                    'image' => json_encode(['productImages/bep-tu-bosch-pkf645e14e.jpg']),
                    'price' => 11500000,
                    'status' => 1,
                    'sale' => 12,
                    'detail' => 'Chức năng: Khóa bàn phím và hẹn giờ bật giờ tắt bếp.',
                    'quantity' => 70,
                    'quantity_sold' => 12
                ]
            ];

            foreach ($products as $p) {
                DB::table('products')->insert($p);
            }
            echo "✅ Products seeded.\n";
        }
    }
}
