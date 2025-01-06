<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViDienTuNguoiDung extends Model
{
    use HasFactory;
    protected $table = 'vi_dien_tu_nguoi_dungs';
    protected $fillable = [
        'so_du',
        'nguoi_dung_id',
        'tinh_trang',
    ];
}
