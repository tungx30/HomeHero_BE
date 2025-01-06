<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $table = 'nguoi_dungs';
    protected $fillable = [
        'email',
        'password',
        'ho_va_ten',
        'hinh_anh',
        'ngay_sinh',
        'gioi_tinh',
        'so_dien_thoai',
        'tinh_trang',
        'hash_reset',
        'hash_active',
        'is_active',
        'is_block',
        'is_master',
    ];
}
