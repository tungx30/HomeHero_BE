<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $table = 'admins';
    protected $fillable = [
        'email',
        'hinh_anh',
        'password',
        'ho_va_ten',
        'ngay_sinh',
        'gioi_tinh',
        'so_dien_thoai',
        'dia_chi',
        'tinh_trang',
        'is_master',
        'hash_reset',
    ];
    // public function getGioiTinhAttribute($value)
    // {
    //     return $value ? 'Nam' : 'Nữ';
    // }
    // public function setGioiTinhAttribute($value)
    // {
    //     // Nếu dữ liệu là string "Nam" hoặc "Nữ", ta chuyển đổi thành boolean
    //     $this->attributes['gioi_tinh'] = strtolower($value) === 'nam' ? 1 : 0;
    // }
}
