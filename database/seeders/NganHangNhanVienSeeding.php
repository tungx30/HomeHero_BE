<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NganHangNhanVienSeeding extends Seeder
{
    public function run(): void
    {
        DB::table('ngan_hang_nhan_viens')->delete();
        DB::table('ngan_hang_nhan_viens')->truncate();
        DB::table('ngan_hang_nhan_viens')->insert([
            [
                'nhan_vien_id'       => 4,
                'stk'                => 1024869643,
                'ten_ngan_hang'      => 'VietComBank',
                'qrRut'             =>'https://img.vietqr.io/image/VCB-1024869643-qr_only.png',
            ],
        ]);
    }
}
