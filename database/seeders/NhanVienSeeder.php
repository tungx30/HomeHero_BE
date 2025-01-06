<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class NhanVienSeeder extends Seeder
{
    private $tinhThanh = [
        'Hà Nội' => ['Ba Đình', 'Hoàn Kiếm', 'Tây Hồ', 'Cầu Giấy', 'Đống Đa', 'Thanh Xuân', 'Hà Đông'],
        'TP Hồ Chí Minh' => ['Quận 1', 'Quận 2', 'Quận 3', 'Quận 7', 'Quận Thủ Đức', 'Quận Bình Thạnh', 'Quận Gò Vấp'],
        'Đà Nẵng' => ['Hải Châu', 'Thanh Khê', 'Liên Chiểu', 'Ngũ Hành Sơn', 'Cẩm Lệ', 'Sơn Trà'],
        'Hải Phòng' => ['Hồng Bàng', 'Ngô Quyền', 'Lê Chân', 'Kiến An', 'Dương Kinh'],
        'Cần Thơ' => ['Ninh Kiều', 'Cái Răng', 'Bình Thủy', 'Ô Môn', 'Thốt Nốt']
    ];

    private $phuongXa = [
        'Phường 1', 'Phường 2', 'Phường 3', 'Phường 4', 'Hòa Khánh Nam', 'Hòa Minh', 'Mỹ Đình 1', 'Mỹ Đình 2'
    ];

    private $duongPho = [
        'Nguyễn Văn Linh', 'Lê Lợi', 'Hùng Vương', 'Phạm Ngũ Lão', 'Trần Hưng Đạo',
        'Hoàng Diệu', 'Nguyễn Du', 'Hai Bà Trưng', 'Lý Thường Kiệt', 'Nguyễn Thị Minh Khai'
    ];

    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $tinhThanh = array_rand($this->tinhThanh);
            $quanHuyen = $this->tinhThanh[$tinhThanh][array_rand($this->tinhThanh[$tinhThanh])];
            $phuongXa = $this->phuongXa[array_rand($this->phuongXa)];
            $duongPho = $this->duongPho[array_rand($this->duongPho)];
            $soNha = rand(1, 999);

            $diaChi = "$soNha $duongPho, $phuongXa, $quanHuyen, $tinhThanh";

            DB::table('nhan_viens')->insert([
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'ho_va_ten' => $this->generateName(),
                'hinh_anh' => fake()->imageUrl(200, 200, 'people', true, 'NhanVien'),
                'can_cuoc_cong_dan' => fake()->numerify('0###########'),
                'ngay_sinh' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'gioi_tinh' => fake()->randomElement([0, 1, 2]),
                'so_dien_thoai' => fake()->numerify('0#########'),
                'dia_chi' => $diaChi,
                'tuoi_nhan_vien' => now()->year - fake()->year(),
                'kinh_nghiem' => $this->generateKinhNghiem(),
                'tinh_trang' => fake()->randomElement([0, 1]),
                'is_noi_bat' => fake()->randomElement([0, 1]),
                'is_flash_sale' => fake()->randomElement([0, 1]),
                'is_master' => 0,
                'id_quyen' => fake()->optional()->numberBetween(1, 5),
                'so_du_vi' => fake()->randomFloat(2, 100000, 1000000),
                'hash_reset' => null,
                'is_block' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function generateName()
    {
        $ho = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng'];
        $tenDem = ['Văn', 'Thị', 'Hữu', 'Xuân', 'Thanh', 'Minh', 'Hoài', 'Thảo', 'Quốc', 'Hồng'];
        $ten = ['Tùng', 'Hòa', 'Anh', 'Bảo', 'Khang', 'Ngọc', 'Linh', 'Huy', 'Trang', 'Dũng'];

        return $ho[array_rand($ho)] . ' ' . $tenDem[array_rand($tenDem)] . ' ' . $ten[array_rand($ten)];
    }

    private function generateKinhNghiem()
    {
        $years = rand(0, 20);
        $months = rand(0, 11);
        if ($years > 0 && $months > 0) {
            return "$years năm $months tháng";
        } elseif ($years > 0) {
            return "$years năm";
        } else {
            return "$months tháng";
        }
    }
}
