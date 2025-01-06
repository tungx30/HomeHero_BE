<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    use HasFactory;
    protected $table = 'thong_baos';
    protected $fillable = [
        'loi_nhan',
        'id_nguoi_gui',
        'loai_nguoi_nhan',
        'loai_nguoi_gui',
        'id_don_hang',
        'id_nguoi_nhan',
        'so_tien_rut',
        'types',
        'status',
    ];
}
