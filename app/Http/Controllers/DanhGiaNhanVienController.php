<?php

namespace App\Http\Controllers;

use App\Http\Requests\DanhGiaNhanVienRequest;
use App\Models\DanhGiaNhanVien;
use App\Models\DonHang;
use App\Models\NhanVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DanhGiaNhanVienController extends Controller
{
    public function store(DanhGiaNhanVienRequest $request, $id_don_hang)
    {
        $khachHang = Auth::guard('sanctum')->user();
        $donHang = DonHang::find($id_don_hang);
        if ($donHang && $donHang->tinh_trang_don_hang == 3) {
            // Giải mã mảng nhân viên từ chuỗi JSON
            $nhanVienIds = json_decode($donHang->nhan_vien_id, true);
            if (is_array($nhanVienIds) && count($nhanVienIds) > 0) {
                foreach ($nhanVienIds as $nhanVienId) {
                    // Kiểm tra xem khách hàng đã đánh giá nhân viên này cho đơn hàng này chưa
                    $kiemTraDanhGia = DanhGiaNhanVien::where('nguoi_dung_id', $khachHang->id)
                        ->where('nhan_vien_id', $nhanVienId)
                        ->where('don_hang_id', $donHang->id)
                        ->first();
                    if (!$kiemTraDanhGia) {
                        DanhGiaNhanVien::create([
                            'nhan_xet'      => $request->nhan_xet,
                            'so_sao'        => $request->so_sao, // Số sao đánh giá từ request
                            'nguoi_dung_id' => $khachHang->id,
                            'nhan_vien_id'  => $nhanVienId,
                            'don_hang_id'   => $donHang->id,
                        ]);
                        $nhanVien = NhanVien::find($nhanVienId);
                        if ($nhanVien) {
                            $tongSoSao = DanhGiaNhanVien::where('nhan_vien_id', $nhanVienId)->sum('so_sao');
                            $soLuotDanhGia = DanhGiaNhanVien::where('nhan_vien_id', $nhanVienId)->count();
                            $nhanVien->tong_so_sao = $soLuotDanhGia > 0 ? $tongSoSao / $soLuotDanhGia : 0;
                            $nhanVien->save();
                        }
                    } else {
                        return response()->json([
                            'status'    => false,
                            'message'   => 'Bạn chỉ có thể đánh giá dịch vụ 1 lần!',
                        ]);
                    }
                }
                return response()->json([
                    'status'  => true,
                    'message' => 'Đã đánh giá đơn dịch thành công . Xin cảm ơn quý khách đã tin tưởng và sử dụng dịch vụ ở chúng tôi!',
                ]);
            } else {
                return response()->json([
                    'status'  => false,
                    'message' => 'Không tìm thấy nhân viên trong đơn hàng này!',
                ]);
            }
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Đơn hàng không tồn tại hoặc chưa hoàn thành nên bạn không thể đánh giá!',
            ]);
        }
    }

    public function getData($id_nhan_vien)
    {
        // Lấy tất cả đánh giá của nhân viên cùng với thông tin người đánh giá
        $data = DanhGiaNhanVien::where('danh_gia_nhan_viens.nhan_vien_id', $id_nhan_vien)
            ->join('nhan_viens', 'danh_gia_nhan_viens.nhan_vien_id', '=', 'nhan_viens.id')
            ->join('nguoi_dungs', 'danh_gia_nhan_viens.nguoi_dung_id', '=', 'nguoi_dungs.id')
            ->select(
                'danh_gia_nhan_viens.*',
                'nhan_viens.ho_va_ten as ten_nhan_vien',
                'nguoi_dungs.ho_va_ten as ten_nguoi_dung'
            )
            ->get();

        return response()->json([
            'data' => $data,
        ]);
    }
    public function getDataNV()
    {
        $nhanVien = Auth::guard('sanctum')->user();
        $data = DanhGiaNhanVien::where('danh_gia_nhan_viens.nhan_vien_id', $nhanVien->id)
            ->join('nhan_viens', 'danh_gia_nhan_viens.nhan_vien_id', '=', 'nhan_viens.id')
            ->join('nguoi_dungs', 'danh_gia_nhan_viens.nguoi_dung_id', '=', 'nguoi_dungs.id')
            ->select(
                'danh_gia_nhan_viens.*',
                'nhan_viens.ho_va_ten as ten_nhan_vien',
                'nguoi_dungs.ho_va_ten as ten_nguoi_dung'
            )
            ->get();

        return response()->json([
            'data' => $data,
        ]);
    }
    public function countDanhGia($idNhanVien)
    {
        $nhanVien = Auth::guard('sanctum')->user();
        $data = DanhGiaNhanVien::where('nhan_vien_id', $idNhanVien)->count();
        return response()->json([
            'data' => $data,
        ]);
    }
}
