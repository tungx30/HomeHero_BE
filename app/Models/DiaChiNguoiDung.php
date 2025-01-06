<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaChiNguoiDung extends Model
{
    use HasFactory;
    protected $table = 'dia_chi_nguoi_dungs';
    protected $fillable = [
        'dia_chi',
        'ten_nguoi_nhan',
        'so_dien_thoai',
        'id_nguoi_dung',
    ];
}
