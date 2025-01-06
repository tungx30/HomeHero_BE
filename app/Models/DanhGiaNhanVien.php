<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhGiaNhanVien extends Model
{
    use HasFactory;
    protected $table = 'danh_gia_nhan_viens';
    protected $fillable = [
        'nhan_vien_id',
        'nguoi_dung_id',
        'don_hang_id',
        'so_sao',
        'nhan_xet',
    ];
}
