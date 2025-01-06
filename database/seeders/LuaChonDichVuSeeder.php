<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class LuaChonDichVuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DB::table('lua_chon_dich_vus')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('lua_chon_dich_vus')->truncate();
        DB::table('lua_chon_dich_vus')->insert([
            [
                'ten_lua_chon' => 'Thuê Giúp Việc Theo Giờ',
                'slug_dich_vu' => Str::slug('Thuê Theo Giờ'),
                'icon_dich_vu' => 'hourly_icon.png',
                'id_muc' => 1, // Giả sử mục dịch vụ này có id là 1
                'is_active' => 1
            ],
            [
                'ten_lua_chon' => 'Thuê Giúp Việc Định Kì',
                'slug_dich_vu' => Str::slug('Thuê Theo Định Kì'),
                'icon_dich_vu' => 'periodic_icon.png',
                'id_muc' => 3, // Giả sử mục dịch vụ này có id là 1
                'is_active' => 1
            ],
            [
                'ten_lua_chon' => 'Tổng Vệ Sinh',
                'slug_dich_vu' => Str::slug('Tổng Vệ Sinh'),
                'icon_dich_vu' => 'deep_cleaning_icon.png',
                'id_muc' => 2, // Giả sử mục dịch vụ này có id là 2
                'is_active' => 1
            ],
            // [
            //     'ten_lua_chon' => 'Vệ Sinh Sofa',
            //     'slug_dich_vu' => Str::slug('Vệ Sinh Sofa'),
            //     'icon_dich_vu' => 'sofa_cleaning_icon.png',
            //     'id_muc' => 2, // Giả sử mục dịch vụ này có id là 2
            //     'is_active' => 1
            // ]
        ]);
    }
}
