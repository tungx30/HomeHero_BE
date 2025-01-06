<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietLichLam extends Model
{
    use HasFactory;
    protected $table = 'chi_tiet_lich_lams';
    protected $fillable = [
        'nhan_vien_id',
        'don_hang_id',
        'ngay_lam_viec',
        'so_gio_phuc_vu',
        'gio_bat_dau',
        'gio_ket_thuc',
        'is_active',
        'is_nhan_lich',
    ];
}
