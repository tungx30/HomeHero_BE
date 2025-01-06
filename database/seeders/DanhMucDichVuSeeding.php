<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class DanhMucDichVuSeeding extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('danh_muc_dich_vus')->truncate();
        // DB::table('danh_muc_dich_vus')->delete();
        DB::table('danh_muc_dich_vus')->insert([
            [
                'ten_muc' => 'Danh mục theo giờ',
                'slug_ten_muc' => Str::slug('Danh mục theo giờ'),
                'so_tien' => 90000,
                'is_active' => 1,
            ],
            [
                'ten_muc' => 'Danh mục theo mét vuông',
                'slug_ten_muc' => Str::slug('Danh mục theo mét vuông'),
                'so_tien' => 758000,
                'is_active' => 1,
            ],
            [
                'ten_muc' => 'Danh mục theo định kì',
                'slug_ten_muc' => Str::slug('Danh mục theo định kì'),
                'so_tien' => 99000,
                'is_active' => 1,
            ],[
                'ten_muc' => 'Danh mục theo Thang ',
                'slug_ten_muc' => Str::slug('Danh mục theo định kì'),
                'so_tien' => 99000,
                'is_active' => 1,
            ],
        ]);
    }
}
