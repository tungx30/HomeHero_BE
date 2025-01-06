<?php

namespace App\Http\Controllers;

use App\Events\ThongBaoEvent;
use App\Models\Admin;
use App\Models\DonHang;
use App\Models\ThongBao;
use App\Models\ViDienTu;
use App\Models\ViDienTuNguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThongBaoController extends Controller
{
    public function getDataNhanDonTuNhanVien(Request $request)
    {
        $khachHang = Auth::guard('sanctum')->user();
        if (!$khachHang) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy thông tin người dùng.'
            ]);
        }

        // Thực hiện JOIN giữa ThongBao và DonHang
        $thongBaos = ThongBao::join('don_hangs', 'thong_baos.id_don_hang', '=', 'don_hangs.id')
            ->select(
                'thong_baos.*',  // Lấy tất cả các cột từ bảng ThongBao
                'don_hangs.id_dich_vu',
                'don_hangs.phuong_thuc_thanh_toan',
                'don_hangs.is_thanh_toan'
            )
            ->where('thong_baos.id_nguoi_nhan', $khachHang->id)
            ->where('thong_baos.types', 1)
            ->orderBy('thong_baos.created_at', 'desc')
            ->get();

        if ($thongBaos->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Không có thông báo nào từ người dùng này.'
            ]);
        }

        // Tạo dữ liệu trả về dưới dạng array chứa thông báo và đơn hàng
        // $dataThongBaos = $thongBaos->map(function ($thongBao) {
        //     return [
        //         'thong_bao' => $thongBao,  // Thông báo
        //     ];
        // });

        return response()->json([
            'status' => true,
            'message' => 'Lấy thông báo thành công.',
            'data' => $thongBaos
        ]);
    }

    public function getDataNhanDonTuNguoiDung(Request $request)
    {
        $nhanVien = Auth::guard('sanctum')->user();
        if (!$nhanVien) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy nhân viên.'
            ]);
        }

        // Lấy thông báo dựa vào điều kiện từ đơn hàng
        $thongBaos = ThongBao::select('thong_baos.*', 'don_hangs.id_dich_vu', 'don_hangs.phuong_thuc_thanh_toan', 'don_hangs.is_thanh_toan')
            ->join('don_hangs', 'thong_baos.id_don_hang', '=', 'don_hangs.id')
            ->where('thong_baos.id_nguoi_nhan', $nhanVien->id)
            ->where('thong_baos.types', 2) // Chỉ lấy thông báo loại 2
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereIn('don_hangs.phuong_thuc_thanh_toan', [0, 2]) // Online hoặc ví
                      ->where('don_hangs.is_thanh_toan', 1); // Chỉ nhận nếu đã thanh toán
                })
                ->orWhere('don_hangs.phuong_thuc_thanh_toan', 1); // Tiền mặt luôn được nhận
            })
            ->orderBy('thong_baos.created_at', 'desc')
            ->get();

        if ($thongBaos->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Không có thông báo nào.'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Lấy thông báo thành công.',
            'data' => $thongBaos
        ]);
    }
        public function taoThongBaoRutTienChoNhanVien(Request $request)
    {
        $nhanVien = Auth::guard('sanctum')->user();
        $viDienTu = ViDienTu::where('nhan_vien_id', $nhanVien->id)->first();
        if (!$viDienTu || $request->so_tien_rut > $viDienTu->so_du) {
            return response()->json([
                'status' => false,
                'message' => 'Số tiền rút vượt quá số dư hiện có.'
            ]);
        }
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $thongBao = ThongBao::create([
                'loi_nhan' => 'Nhân viên ' . $nhanVien->ho_va_ten . ' gửi yêu cầu rút tiền với số tiền rút ' . $request->so_tien_rut . '',
                'id_nguoi_gui' => $nhanVien->id,
                'id_nguoi_nhan' => $admin->id,
                'so_tien_rut' => $request->so_tien_rut,
                'loai_nguoi_gui' => 1,
                'types' => 3,
                'status' => 1,
            ]);
            broadcast(new ThongBaoEvent($thongBao));
        }
        $viDienTu->so_du -= $request->so_tien_rut;
        $viDienTu->save();
        return response()->json([
            'status' => true,
            'message' => 'Yêu cầu rút tiền từ ví của bạn đã được gửi đến Quản Trị Viên Thành Công .Hãy đợi Quản Trị Viên để được giải ngân.'
        ]);
    }
    public function getDataRutTienNV()
    {
        $admin = Auth::guard('sanctum')->user();
        $thongBaos = ThongBao::where('id_nguoi_nhan', $admin->id)
            ->where('types', 3)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($thongBaos->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Không có thông báo nào.'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Lấy thông báo rút tiền thành công.',
            'data' => $thongBaos
        ]);
    }
    public function taoThongBaoRutTienChoNguoiDung(Request $request)
    {
        $nguoiDung = Auth::guard('sanctum')->user();
        $viDienTu = ViDienTuNguoiDung::where('nguoi_dung_id', $nguoiDung->id)->first();
        if (!$viDienTu || $request->so_tien_rut > $viDienTu->so_du) {
            return response()->json([
                'status' => false,
                'message' => 'Số tiền rút vượt quá số dư hiện có.'
            ]);
        }
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $thongBao = ThongBao::create([
                'loi_nhan' => 'Người dùng ' . $nguoiDung->ho_va_ten . ' gửi yêu cầu rút tiền với số tiền rút ' . $request->so_tien_rut . '',
                'id_nguoi_gui' => $nguoiDung->id,
                'id_nguoi_nhan' => $admin->id,
                'so_tien_rut' => $request->so_tien_rut,
                'loai_nguoi_gui' => 2,
                'types' => 3,
                'status' => 1,
            ]);
            broadcast(new ThongBaoEvent($thongBao));
        }
        $viDienTu->so_du -= $request->so_tien_rut;
        $viDienTu->save();
        return response()->json([
            'status' => true,
            'message' => 'Yêu cầu rút tiền từ ví của bạn đã được gửi đến Quản Trị Viên Thành Công .Hãy đợi Quản Trị Viên để được giải ngân.'
        ]);
    }
    public function changeIsRead($id_thong_bao)
    {
        $thongBao = ThongBao::find($id_thong_bao);
        if (!$thongBao) {
            return response()->json([
                'status' => false,
                'message' => 'Thông báo không tồn tại.'
            ]);
        }

        $thongBao->is_read = 1;
        if ($thongBao->save()) {
            return response()->json([
                'status' => true,
                'message' => 'Đã đọc thông báo thành công.'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Không thể cập nhật trạng thái thông báo.'
        ]);
    }
}
