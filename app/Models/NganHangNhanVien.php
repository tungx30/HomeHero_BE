<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NganHangNhanVien extends Model
{
    use HasFactory;
    protected $table = 'ngan_hang_nhan_viens';
    protected $fillable = [
       'nhan_vien_id',
        'stk',
        'ten_ngan_hang',
        'qrRut',
    ];
}
