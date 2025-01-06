<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhMucDichVu extends Model
{
    use HasFactory;
    protected $table = 'danh_muc_dich_vus';
    protected $fillable = [
       'ten_muc',
       'slug_ten_muc',
       'so_tien',
       'is_active'
    ];
}
