<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admins')->delete();
        DB::table('admins')->truncate();
        DB::table('admins')->insert([
           [
            'email'             => 'nguyendien1804@gmail.com',
            'password'          => bcrypt('123456'), // Mật khẩu đã mã hóa
            'ho_va_ten'         => 'Nguyễn Thanh Tùng',
            'ngay_sinh'         => '2003-08-30',
            'gioi_tinh'         => 1, // 1 là Nam, 0 là Nữ
            'so_dien_thoai'     => '0369396097',
            'dia_chi'           => '09 Nguyễn Minh Chấn , Hòa Khánh Nam , Liên Chiểu , Đà Nẵng',
            'tinh_trang'        => 1, // 1 là hoạt động, 0 là không hoạt động
           ],
        ]);
    }
}
