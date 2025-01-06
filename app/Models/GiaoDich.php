<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiaoDich extends Model
{
    use HasFactory;
    protected $table='giao_diches';
    protected $fillable = [
        'creditAmount',
        'debitAmount',
        'description',
        'refNo',
        'id_don_hang',
        'nguoi_dung_id',
        'type',
        'is_duyet',
        'is_done',
        'id_thong_bao',
    ];
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id', 'id');
    }
}
