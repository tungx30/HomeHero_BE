<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    use HasFactory;
    protected $table = 'don_hangs';
    protected $fillable = [
        'ma_don_hang',
        'id_dich_vu',
        'nhan_vien_id',
        'nguoi_dung_id',
        'id_dia_chi',
        'so_luong_nv',
        'so_tang_phuc_vu',
        'so_gio_phuc_vu',
        'gio_bat_dau_lam_viec',
        'gio_ket_thuc_lam_viec',
        'so_ngay_phuc_vu_hang_tuan',
        'ngay_bat_dau_lam',
        'tong_so_buoi_phuc_vu_theo_so_thang_phuc_vu',
        'so_thang_phuc_vu',
        'loai_nha',
        'dien_tich_tong_san',
        'tong_tien',
        'ma_code_giam',
        'so_tien_giam',
        'so_tien_thanh_toan',
        'is_thanh_toan',
        'tinh_trang_don_hang',
        'ghi_chu',
        'phuong_thuc_thanh_toan',
        'is_da_tinh_luong',
    ];
}
