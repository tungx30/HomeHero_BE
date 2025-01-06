<?php

namespace App\Http\Controllers;

use App\Models\NganHangNhanVien;
use App\Models\NhanVien;
use App\Models\ViDienTu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NganHangNhanVienController extends Controller
{
    public function store(Request $request,$id_nhan_vien)
    {
        $check = NganHangNhanVien::create([
            'nhan_vien_id'      => $id_nhan_vien,
            'stk'               => $request->stk,
            'ten_ngan_hang'     => $request->ten_ngan_hang,
            'qrRut'             => $request->qrRut
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Đã tạo tài khoản ngân hàng nhân viên thành công.',
            'data' => $check,
        ]);
    }
    public function checkTK(Request $request)
    {
        $nhanVien = Auth::guard('sanctum')->user();
        $taiKhoan = NganHangNhanVien::where('nhan_vien_id', $nhanVien->id)->first();
        if ($taiKhoan) {
            if ($taiKhoan->qrRut) {
                // Người dùng đã có tài khoản ngân hàng và mã qrRut => cho phép sử dụng ví
                return response()->json([
                    'status' => true,
                    'message' => 'Nhân Viên đã có tài khoản ngân hàng và mã QR đã được thiết lập.',
                    'data' => $taiKhoan,
                ]);
            } else {
                // // Có tài khoản NH nhưng chưa có mã qrRut
                // // Gửi thông báo cho admin để cập nhật mã QR
                $loiNhan = "Nhân viên " . $nhanVien->ho_va_ten . "+ căn cước công dân :  " . $nhanVien->can_cuoc_cong_dan . ". Cần được cập nhật mã QR để sử dụng ví.";
                $this->taoThongBaoChoAdminCapNhatQR($loiNhan,$nhanVien->id);
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản ngân hàng đã có nhưng chưa được cập nhật mã QR. Vui lòng liên hệ quản trị viên cập nhật.',
                ]);
            }
        } else {
            $loiNhan = "Nhân viên " . $nhanVien->ho_va_ten . "+ căn cước công dân :  " . $nhanVien->can_cuoc_cong_dan . ". Cần được cập nhật tài khoản ngân hàng và mã QR để sử dụng ví.";
            $this->taoThongBaoChoAdminCapNhatQR($loiNhan,$nhanVien->id);
            return response()->json([
                'status' => false,
                'message' => 'Bạn cần phải có tài khoản ngân hàng để sử dụng ví . Vui lòng liên hệ công ty để cập nhật.',
            ]);
        }
    }
    public function getData($id_nhan_vien)
    {
        // Kiểm tra nếu id_nhan_vien không được cung cấp
        if (!$id_nhan_vien) {
            return response()->json([
                'message' => 'Nhan vien ID is required.',
            ], 400);
        }

        // Lấy thông tin chi tiết nhân viên và ngân hàng (LEFT JOIN)
        $taiKhoan = NhanVien::leftJoin('ngan_hang_nhan_viens', 'nhan_viens.id', '=', 'ngan_hang_nhan_viens.nhan_vien_id')
            ->select(
                'nhan_viens.id as nhan_vien_id',
                'nhan_viens.ho_va_ten',
                'nhan_viens.email',
                'nhan_viens.can_cuoc_cong_dan',
                'nhan_viens.ngay_sinh',
                'nhan_viens.gioi_tinh',
                'nhan_viens.so_dien_thoai',
                'nhan_viens.dia_chi',
                'ngan_hang_nhan_viens.id as ngan_hang_id', // ID ngân hàng (nếu có)
                'ngan_hang_nhan_viens.ten_ngan_hang',
                'ngan_hang_nhan_viens.stk',
                'ngan_hang_nhan_viens.qrRut'
            )
            ->where('nhan_viens.id', $id_nhan_vien)
            ->first();

        // Nếu không tìm thấy nhân viên
        if (!$taiKhoan) {
            return response()->json([
                'message' => 'Không tìm thấy nhân viên với ID này.',
            ], 404);
        }

        // Trả về thông tin nhân viên và ngân hàng (nếu có)
        return response()->json([
            'data' => $taiKhoan,
        ]);
    }
    public function getDataAll(Request $request)
    {
        $taiKhoan = ViDienTu::join('ngan_hang_nhan_viens', 'vi_dien_tus.nhan_vien_id', '=', 'ngan_hang_nhan_viens.nhan_vien_id')
            ->join('nhan_viens', 'vi_dien_tus.nhan_vien_id', '=', 'nhan_viens.id')
            ->select(
                'ngan_hang_nhan_viens.*',
                'vi_dien_tus.so_du',
                'nhan_viens.email',
                'nhan_viens.can_cuoc_cong_dan',
                'nhan_viens.ho_va_ten'
            )
            ->get();

        return response()->json([
            'data' => $taiKhoan,
        ]);
    }
    public function update(Request $request, $id_ngan_hang)
    {
        // Sử dụng updateOrCreate để cập nhật hoặc tạo mới bản ghi
        $check = NganHangNhanVien::where('id', $id_ngan_hang)->update(
            [
                'stk'           => $request->stk,
                'ten_ngan_hang' => $request->ten_ngan_hang,
                'qrRut'         => $request->qrRut
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Đã cập nhật tài khoản ngân hàng nhân viên thành công.',
            'data'    => $check,
        ]);
    }

    public function congTien(Request $request)
    {
        // Debug nhan_vien_id từ request
        Log::info('Request nhan_vien_id: ' . $request->nhan_vien_id);
        // Lấy tài khoản ví điện tử
        $taiKhoan = ViDienTu::where('nhan_vien_id', $request->nhan_vien_id)->first();

        if ($taiKhoan) {
            $taiKhoan->so_du += $request->so_tien;
            $taiKhoan->save();
            return response()->json([
                'status' => true,
                'data' => $taiKhoan,
                'message' => 'Đã cộng tiền thành công.',
            ]);
        } else {
            // Debug khi không tìm thấy tài khoản
            Log::warning('Không tìm thấy tài khoản với nhan_vien_id: ' . $request->nhan_vien_id);
            return response()->json([
                'data' => null,
                'message' => 'Bạn cần phải cung cấp tài khoản ngân hàng để sử dụng ví',
            ]);
        }
    }

    public function truTien(Request $request)
    {
        $taiKhoan = ViDienTu::where('nhan_vien_id', $request->nhan_vien_id)->first();
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
