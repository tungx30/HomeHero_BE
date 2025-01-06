<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiaoDichNhanVien extends Model
{
    use HasFactory;
    protected $table = 'giao_dich_nhan_viens';
    protected $fillable = [
        'creditAmount',
        'debitAmount',
        'description',
        'refNo',
        'nhan_vien_id',
        'type',
        'is_duyet',
        'is_done',
        'id_thong_bao',
    ];
    public function nhanVien()
    {
        return $this->belongsTo(NhanVien::class, 'nhan_vien_id', 'id');
    }
}
