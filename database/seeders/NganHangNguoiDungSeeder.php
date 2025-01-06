<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NganHangNguoiDungSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('t_k_ngan_hang_nguoi_dungs')->delete();
        DB::table('t_k_ngan_hang_nguoi_dungs')->delete();
        DB::table('t_k_ngan_hang_nguoi_dungs')->insert([
            [
                'nguoi_dung_id'       => 3,
                'stk'                => '0369396097',
                'ten_ngan_hang'      => 'TPBank',
                'qrRut'             =>'https://img.vietqr.io/image/TPB-0369396097-qr_only.png',
            ],
        ]);
    }
}
