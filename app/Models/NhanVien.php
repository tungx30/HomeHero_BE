<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class NhanVien extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $table = 'nhan_viens';
    protected $fillable = [
        'email',
        'password',
        'ho_va_ten',
        'hinh_anh',
        'can_cuoc_cong_dan',
        'ngay_sinh',
        'gioi_tinh',
        'so_dien_thoai',
        'dia_chi',
        'tuoi_nhan_vien',
        'kinh_nghiem',
        'tinh_trang',
        'is_noi_bat',
        'is_flash_sale',
        'is_master',
        'id_quyen',
        'so_du_vi',
        'hash_reset',
        'is_block',
    ];

}
