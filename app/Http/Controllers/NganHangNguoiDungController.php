<?php

namespace App\Http\Controllers;

use App\Models\TKNganHangNguoiDung;
use App\Models\ViDienTuNguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NganHangNguoiDungController extends Controller
{
    public function store(Request $request)
    {
        $tai_khoan_dang_dang_nhap   = Auth::guard('sanctum')->user();
        $check = TKNganHangNguoiDung::create([
            'nguoi_dung_id'     => $tai_khoan_dang_dang_nhap->id,
            'stk'               => $request->stk,
            'ten_ngan_hang'     => $request->ten_ngan_hang,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Đã tạo tài khoản thành công. Hãy đợi hệ thống cập nhật ',
            'data' => $check,
        ]);
    }
    public function checkTK(Request $request)
    {
        $nguoiDungId = Auth::guard('sanctum')->user();
        $taiKhoan = TKNganHangNguoiDung::where('nguoi_dung_id', $nguoiDungId->id)->first();
        if ($taiKhoan) {
            if ($taiKhoan->qrRut) {
                // Người dùng đã có tài khoản ngân hàng và mã qrRut => cho phép sử dụng ví
                return response()->json([
                    'status' => true,
                    'message' => 'Người dùng đã có tài khoản ngân hàng và mã QR đã được thiết lập.',
                    'data' => $taiKhoan,
                ]);
            } else {
                // // Có tài khoản NH nhưng chưa có mã qrRut
                // // Gửi thông báo cho admin để cập nhật mã QR
                $loiNhan = "Người dùng " . $nguoiDungId->ho_va_ten . "+ số điện thoại  " . $nguoiDungId->so_dien_thoai . ". cần được cập nhật mã QR để sử dụng ví.";
                $this->taoThongBaoChoAdminCapNhatQR($loiNhan,$nguoiDungId->id);
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản ngân hàng đã có nhưng chưa được cập nhật mã QR. Vui lòng chờ quản trị viên cập nhật.',
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Bạn cần phải cung cấp tài khoản ngân hàng để sử dụng ví',
            ]);
        }
    }
    public function getData()
    {
        $taiKhoan = ViDienTuNguoiDung::join('t_k_ngan_hang_nguoi_dungs', 'vi_dien_tu_nguoi_dungs.nguoi_dung_id', '=', 't_k_ngan_hang_nguoi_dungs.nguoi_dung_id')
            ->join('nguoi_dungs', 'vi_dien_tu_nguoi_dungs.nguoi_dung_id', '=', 'nguoi_dungs.id')
            ->select(
                't_k_ngan_hang_nguoi_dungs.*',
                'vi_dien_tu_nguoi_dungs.so_du',
                'nguoi_dungs.email',
                'nguoi_dungs.so_dien_thoai',
                'nguoi_dungs.ho_va_ten'
            )
            ->get();

        return response()->json([
            'data' => $taiKhoan,
        ]);
    }
    
    public function updateQR(Request $request)
    {
        $qrNguoiDung= TKNganHangNguoiDung::where('nguoi_dung_id',$request->nguoi_dung_id)->update(['qrRut'=>$request->qrRut]);
        // $qrNguoiDung->save();
        return response()->json([
           'status' => true,
           'message' => 'Đã cập nhật mã QR thành công.',
        ]);
    }
    public function congTien(Request $request)
    {
        $taiKhoan = ViDienTuNguoiDung::where('nguoi_dung_id', $request->nguoi_dung_id)->first();
        if ($taiKhoan) {
            $taiKhoan->so_du += $request->so_tien;
            $taiKhoan->save();
            return response()->json([
                'status' => true,
                'data' => $taiKhoan,
                'message' => 'Đã cộng tiền thành công.',
            ]);
        } else {
            return response()->json([
                'data' => null,
                'message' => 'Bạn cần phải cung cấp tài khoản ngân hàng để sử dụng ví',
            ]);
        }
    }
    public function truTien(Request $request)
    {
        $taiKhoan = ViDienTuNguoiDung::where('nguoi_dung_id', $request->nguoi_dung_id)->first();
        if ($taiKhoan && $taiKhoan->so_du >= $request->so_tien) {
            $taiKhoan->so_du -= $request->so_tien;
            $taiKhoan->save();
            return response()->json([
                'status' => true,
                'data' => $taiKhoan,
                'message' => 'Đã trừ tiền thành công.',
            ]);
        } else {
            return response()->json([
                'data' => null,
                'message' => 'Số dư không đủ để thực hiện giao dịch.',
            ]);
        }
    }
}
