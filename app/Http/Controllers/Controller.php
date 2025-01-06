<?php

namespace App\Http\Controllers;

use App\Events\ThongBaoEvent;
use App\Models\Admin;
use App\Models\NguoiDung;
use App\Models\NhanVien;
use App\Models\ThongBao;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    public function taoThongBaoChoNhanVienNhanDon($loiNhan, $idNguoiGui, $idDonHang = null)
    {
        $nhanViens = NhanVien::all();
        foreach ($nhanViens as $nhanVien) {
            $thongBao = ThongBao::create([
                'loi_nhan' => $loiNhan,
                'id_nguoi_gui' => $idNguoiGui,
                'id_nguoi_nhan' => $nhanVien->id,
                'id_don_hang' => $idDonHang,
                'loai_nguoi_gui' => 2,
                'types' => 2, // Thông báo của nhân viên
                'status' => 1,
            ]);
            broadcast(new ThongBaoEvent($thongBao));
        }
    }
    public function taoThongBaoChoNguoiDungDatDon($loiNhan, $idNguoiGui, $idNguoiNhan, $idDonHang = null)
    {
        $thongBao = ThongBao::create([
            'loi_nhan' => $loiNhan,
            'id_nguoi_gui' => $idNguoiGui,
            'id_nguoi_nhan' => $idNguoiNhan,
            'id_don_hang' => $idDonHang,
            'loai_nguoi_gui' => 1,
            'types' => 1, // Thông báo của người dùng
            'status' => 1,
        ]);
        broadcast(new ThongBaoEvent($thongBao));
    }
    public function taoThongBaoChoAdminMaGiamGia($loiNhan, $idNguoiGui, $idDonHang = null)
    {
        $khachHang = NguoiDung::all();
        foreach ($khachHang as $khachhang) {
            $thongBao = ThongBao::create([
                'loi_nhan' => $loiNhan,
                'id_nguoi_gui' => $idNguoiGui,
                'id_nguoi_nhan' => $khachhang->id,
                'id_don_hang' => $idDonHang,
                'loai_nguoi_gui' => 3,
                'types' => 1, // Thông báo của người dùng
                'status' => 1,
            ]);
            broadcast(new ThongBaoEvent($thongBao));
        }
    }
    public function taoThongBaoChoAdminCapNhatQR($loiNhan, $idNguoiGui)
    {
        // Giả sử bạn có model Admin để truy vấn danh sách admin
        // Nếu không có, bạn cần chỉnh lại logic này cho phù hợp, chẳng hạn nếu admin cũng là Người Dùng với 1 role nhất định.
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $thongBao = ThongBao::create([
                'loi_nhan' => $loiNhan,
                'id_nguoi_gui' => $idNguoiGui,
                'id_nguoi_nhan' => $admin->id,
                // Dựa trên các hàm mẫu: loai_nguoi_gui = 3 có thể tượng trưng cho admin, bạn cần thống nhất trong hệ thống.
                'loai_nguoi_gui' => 2,
                'types' => 3, // Loại thông báo dành cho admin về việc cập nhật QR (có thể tuỳ chỉnh)
                'status' => 1,
            ]);
            broadcast(new ThongBaoEvent($thongBao));
        }
    }
}
