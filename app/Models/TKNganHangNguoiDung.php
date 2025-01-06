<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TKNganHangNguoiDung extends Model
{
    use HasFactory;
    protected $table = 't_k_ngan_hang_nguoi_dungs';
    protected $fillable = [
       'nguoi_dung_id',
        'stk',
        'ten_ngan_hang',
        'qrRut',
    ];
}
