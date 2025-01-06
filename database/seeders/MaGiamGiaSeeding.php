<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaGiamGiaSeeding extends Seeder
{
    public function run(): void
    {
        DB::table('ma_giam_gias')->delete();
        DB::table('ma_giam_gias')->truncate();
        DB::table('ma_giam_gias')->insert([
            [
                'code' => 'DISCOUNT01',
                'tinh_trang' => 1,
                'ngay_bat_dau' => '2024-09-01',
                'ngay_ket_thuc' => '2024-09-30',
                'loai_giam_gia' => 0,
                'so_giam_gia' => 30000,
                'so_tien_toi_da' => 30000,
                'dk_toi_thieu_don_hang' => 150000,
            ],
            [
                'code' => 'DISCOUNT02',
                'tinh_trang' => 0,
                'ngay_bat_dau' => '2024-07-15',
                'ngay_ket_thuc' => '2024-09-15',
                'loai_giam_gia' => 1,
                'so_giam_gia' => 20,
                'so_tien_toi_da' => 50000,
                'dk_toi_thieu_don_hang' => 100000,
            ],
            [
                'code' => 'DISCOUNT03',
                'tinh_trang' => 1,
                'ngay_bat_dau' => '2024-07-20',
                'ngay_ket_thuc' => '2024-09-20',
                'loai_giam_gia' => 0,
                'so_giam_gia' => 15,
                'so_tien_toi_da' => 1500,
                'dk_toi_thieu_don_hang' => 750,
            ],
            [
                'code' => 'DISCOUNT04',
                'tinh_trang' => 0,
                'ngay_bat_dau' => '2024-07-05',
                'ngay_ket_thuc' => '2024-09-25',
                'loai_giam_gia' => 1,
                'so_giam_gia' => 30,
                'so_tien_toi_da' => 3000,
                'dk_toi_thieu_don_hang' => 1500,
            ],
            [
                'code' => 'DISCOUNT05',
                'tinh_trang' => 1,
                'ngay_bat_dau' => '2024-07-09',
                'ngay_ket_thuc' => '2024-09-18',
                'loai_giam_gia' => 0,
                'so_giam_gia' => 25,
                'so_tien_toi_da' => 2500,
                'dk_toi_thieu_don_hang' => 1250,
            ],
            [
                'code' => 'DISCOUNT06',
                'tinh_trang' => 1,
                'ngay_bat_dau' => '2024-07-09',
                'ngay_ket_thuc' => '2024-09-22',
                'loai_giam_gia' => 0,
                'so_giam_gia' => 40,
                'so_tien_toi_da' => 4000,
                'dk_toi_thieu_don_hang' => 2000,
            ],
            [
                'code' => 'DISCOUNT07',
                'tinh_trang' => 0,
                'ngay_bat_dau' => '2024-07-12',
                'ngay_ket_thuc' => '2024-09-12',
                'loai_giam_gia' => 1,
                'so_giam_gia' => 35,
                'so_tien_toi_da' => 3500,
                'dk_toi_thieu_don_hang' => 1750,
            ],
            [
                'code' => 'DISCOUNT08',
                'tinh_trang' => 1,
                'ngay_bat_dau' => '2024-07-09',
                'ngay_ket_thuc' => '2024-09-22',
                'loai_giam_gia' => 0,
                'so_giam_gia' => 40,
                'so_tien_toi_da' => 4000,
                'dk_toi_thieu_don_hang' => 2000,
            ],
            [
                'code' => 'DISCOUNT09',
                'tinh_trang' => 0,
                'ngay_bat_dau' => '2024-07-03',
                'ngay_ket_thuc' => '2024-09-17',
                'loai_giam_gia' => 1,
                'so_giam_gia' => 45,
                'so_tien_toi_da' => 4500,
                'dk_toi_thieu_don_hang' => 2250,
            ],
            [
                'code' => 'DISCOUNT10',
                'tinh_trang' => 0,
                'ngay_bat_dau' => '2024-07-14',
                'ngay_ket_thuc' => '2024-09-14',
                'loai_giam_gia' => 1,
                'so_giam_gia' => 55,
                'so_tien_toi_da' => 5500,
                'dk_toi_thieu_don_hang' => 2750,
            ],
        ]);
    }
}
