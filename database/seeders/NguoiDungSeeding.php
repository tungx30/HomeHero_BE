<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\NguoiDung;
use App\Models\ViDienTuNguoiDung;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NguoiDungSeeding extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('nguoi_dungs')->truncate();
        DB::table('vi_dien_tu_nguoi_dungs')->truncate();
        $nguoiDungs = [
            [
                'email' => 'example1@gmail.com',
                'password' => bcrypt('123456'),
                'ho_va_ten' => 'Nguyen Van A',
                'hinh_anh' => 'path/to/image1.jpg',
                'ngay_sinh' => '1990-01-01',
                'gioi_tinh' => 1,
                'so_dien_thoai' => '0123456789',
                'tinh_trang' => 1,
                'is_active' => 1,
                'is_block' => 0,
            ],
            [
                'email' => 'example2@gmail.com',
                'password' => bcrypt('123456'),
                'ho_va_ten' => 'Tran Thi B',
                'hinh_anh' => null,
                'ngay_sinh' => '1985-05-15',
                'gioi_tinh' => 0,  // Nữ
                'so_dien_thoai' => '0987654321',
                'tinh_trang' => 0,
                'is_active' => 0,
                'is_block' => 0,
            ],
            [
                'email'     => 'nthaonguyen184308@gmail.com',
                'password' => bcrypt('123456'),
                'ho_va_ten' => 'Tran Thi B',
                'hinh_anh' => null,
                'ngay_sinh' => '1985-05-15',
                'gioi_tinh' => 0,
                'so_dien_thoai' => '0369396100',
                'tinh_trang' => 0,
                'is_active' => 0,
                'is_block' => 0,
            ]
        ];
        foreach ($nguoiDungs as $nguoiDung) {
            $createdNguoiDung = NguoiDung::create($nguoiDung);
            ViDienTuNguoiDung::create([
                'nguoi_dung_id' => $createdNguoiDung->id,
                'so_du' => 0,
                'tinh_trang' => 0,
            ]);
        }
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
