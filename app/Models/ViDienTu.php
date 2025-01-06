<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViDienTu extends Model
{
    use HasFactory;
    protected $table = 'vi_dien_tus';
    protected $fillable =[
        'so_tien_nap',
        'so_tien_rut',
        'so_du',
        'nhan_vien_id',
        'tinh_trang',
    ];
}
