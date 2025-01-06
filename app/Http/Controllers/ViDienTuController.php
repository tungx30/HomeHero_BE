<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\NguoiDung;
use App\Models\NhanVien;
use App\Models\ThongBao;
use App\Models\ViDienTu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Console\View\Components\Info;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class ViDienTuController extends Controller
{
    public function getData()
    {
        // Lấy thông tin nhân viên từ token
        $nhanVien = Auth::guard('sanctum')->user();

        if (!$nhanVien) {
            return response()->json([
                'message' => 'Không tìm thấy thông tin nhân viên từ token.'
            ], 404);
        }
        $data = ViDienTu::join('nhan_viens', 'vi_dien_tus.nhan_vien_id', '=', 'nhan_viens.id')
            ->where('vi_dien_tus.nhan_vien_id', $nhanVien->id)
            ->select('vi_dien_tus.*', 'nhan_viens.*') // Lấy toàn bộ thông tin từ cả hai bảng
            ->first();

        if ($data) {
            return response()->json([
                'message' => 'Lấy dữ liệu thành công.',
                'data' => $data,
            ], 200);
        }

        return response()->json([
            'message' => 'Không tìm thấy dữ liệu về ví điện tử hoặc nhân viên.'
        ], 404);
    }
    public function getthanhToanRutTienNV($id_thong_bao)
    {
        // Lấy thông báo từ id
        $thongBao = ThongBao::where('id', $id_thong_bao)->first();
        if (!$thongBao) {
            return response()->json(['message' => 'Thông báo không tồn tại'], 404);
        }

        $idNhanVien = $thongBao->id_nguoi_gui;

        // Lấy thông tin nhân viên và thông tin ngân hàng
        $nhanVienData = NhanVien::where('nhan_viens.id', $idNhanVien)
            ->join('ngan_hang_nhan_viens', 'nhan_viens.id', '=', 'ngan_hang_nhan_viens.nhan_vien_id')
            ->select(
                'nhan_viens.ho_va_ten',
                'nhan_viens.can_cuoc_cong_dan',
                'nhan_viens.ngay_sinh',
                'nhan_viens.so_dien_thoai',
                'ngan_hang_nhan_viens.nhan_vien_id',
                'ngan_hang_nhan_viens.stk',
                'ngan_hang_nhan_viens.ten_ngan_hang',
                'ngan_hang_nhan_viens.qrRut'
            )
            ->first();

        if (!$nhanVienData) {
            return response()->json(['message' => 'Nhân viên hoặc thông tin ngân hàng không tồn tại'], 404);
        }

        // Tạo URL QR code mới
        $qrRut = $this->generateQrCodeUrlToNV(
            $thongBao->so_tien_rut,
            $nhanVienData->ho_va_ten,
            $nhanVienData->can_cuoc_cong_dan,
            $nhanVienData->qrRut
        );

        // Cập nhật QR code trong bảng ngan_hang_nhan_viens
        DB::table('ngan_hang_nhan_viens')
            ->where('nhan_vien_id', $idNhanVien)
            ->update(['qrRut' => $qrRut]);

        // Thêm QR code mới vào kết quả trả về
        $nhanVienData->qrRut = $qrRut;

        return response()->json([
            'thong_bao' => $thongBao,
            'nhan_vien' => $nhanVienData,
        ]);
    }

    private function generateQrCodeUrlToNV($amount, $hoVaTen, $canCuocCongDan, $baseUrl): string
    {
        $parsedUrl = parse_url($baseUrl);
        $baseUrlWithoutParams = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
        $addInfo = urlencode("NV: {$hoVaTen}, CCCD: {$canCuocCongDan}");
        return "{$baseUrlWithoutParams}?amount={$amount}&addInfo={$addInfo}";
    }

    public function getthanhToanRutTienND($id_thong_bao)
    {
        $thongBao = ThongBao::where('id', $id_thong_bao)->first();
        if (!$thongBao) {
            return response()->json(['message' => 'Thông báo không tồn tại'], 404);
        }

        $idNguoiDung = $thongBao->id_nguoi_gui;
        $nguoiDungData = NguoiDung::where('nguoi_dungs.id', $idNguoiDung)
            ->join('t_k_ngan_hang_nguoi_dungs', 'nguoi_dungs.id', '=', 't_k_ngan_hang_nguoi_dungs.nguoi_dung_id')
            ->select(
                'nguoi_dungs.ho_va_ten',
                'nguoi_dungs.ngay_sinh',
                'nguoi_dungs.so_dien_thoai',
                't_k_ngan_hang_nguoi_dungs.nguoi_dung_id',
                't_k_ngan_hang_nguoi_dungs.stk',
                't_k_ngan_hang_nguoi_dungs.ten_ngan_hang',
                't_k_ngan_hang_nguoi_dungs.qrRut'
            )
            ->first();

        if (!$nguoiDungData) {
            return response()->json(['message' => 'Nhân viên hoặc thông tin ngân hàng không tồn tại'], 404);
        }
        $qrRut = $this->generateQrCodeUrlToND(
            $thongBao->so_tien_rut,
            $nguoiDungData->ho_va_ten,
            $nguoiDungData->so_dien_thoai,
            $nguoiDungData->qrRut
        );
        DB::table('t_k_ngan_hang_nguoi_dungs')
            ->where('nguoi_dung_id', $idNguoiDung)
            ->update(['qrRut' => $qrRut]);
        $nguoiDungData->qrRut = $qrRut;

        return response()->json([
            'thong_bao' => $thongBao,
            'nguoiDung' => $nguoiDungData,
        ]);
    }

    private function generateQrCodeUrlToND($amount, $hoVaTen, $sđt, $baseUrl): string
    {
        $parsedUrl = parse_url($baseUrl);
        $baseUrlWithoutParams = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
        $addInfo = urlencode("KH {$hoVaTen}, SĐT: {$sđt}");
        return "{$baseUrlWithoutParams}?amount={$amount}&addInfo={$addInfo}";
    }




}
