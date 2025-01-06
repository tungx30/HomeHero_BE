<?php

namespace App\Http\Controllers;

use App\Models\NhanVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ThongKeNhanVienController extends Controller
{
    public function countSoLuongCV()
    {
        $nhanVien = Auth::guard('sanctum')->user();
        if (!$nhanVien) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy nhân viên đang đăng nhập.'], 401);
        }
        $donHangs = DB::table('don_hangs')
            ->whereRaw('JSON_CONTAINS(nhan_vien_id, ?)', [json_encode($nhanVien->id)])
            ->get();
        $soLuongDonHang = $donHangs->count();
        return response()->json([
            'status' => true,
            'data' => $soLuongDonHang,
        ]);
    }
    public function sumSoGioLamViec()
    {
        $nhanVien = Auth::guard('sanctum')->user();
        if (!$nhanVien) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy nhân viên đang đăng nhập.'], 401);
        }
        $tongSoGioPhucVu = DB::table('don_hangs')
            ->whereRaw('JSON_CONTAINS(nhan_vien_id, ?)', [json_encode($nhanVien->id)])
            ->where('id_dich_vu', '<>', 2)
            ->sum('so_gio_phuc_vu');
        $tongSoGioDinhKy = DB::table('don_hangs')
            ->whereRaw('JSON_CONTAINS(nhan_vien_id, ?)', [json_encode($nhanVien->id)])
            ->where('id_dich_vu', 2)
            ->select(DB::raw('SUM(so_gio_phuc_vu * tong_so_buoi_phuc_vu_theo_so_thang_phuc_vu) as tong_gio_dinh_ky'))
            ->value('tong_gio_dinh_ky');
        $tongSoGioDinhKy = $tongSoGioDinhKy ?: 0;
        $tongSoGioLamViec = $tongSoGioPhucVu + $tongSoGioDinhKy;

        return response()->json([
            'status' => true,
            'data' => $tongSoGioLamViec
        ]);
    }

    public function sumThuNhap()
    {
        $nhanVien = Auth::guard('sanctum')->user();
        if (!$nhanVien) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy nhân viên đang đăng nhập.'], 401);
        }
        $tongTien = DB::table('don_hangs')
            ->whereRaw('JSON_CONTAINS(nhan_vien_id, ?)', [json_encode($nhanVien->id)])
            ->selectRaw('SUM(tong_tien / so_luong_nv) as total')
            ->value('total');
        return response()->json([
            'status' => true,
            'data' => $tongTien
        ]);
    }
    public function getTongSoSao()
    {
        $nhanVien = Auth::guard('sanctum')->user();
        if (!$nhanVien) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy nhân viên đang đăng nhập.'], 401);
        }
        $tongSoSao = NhanVien::where('id', $nhanVien->id)->value('tong_so_sao');
        return response()->json([
            'status' => true,
            'data' => $tongSoSao
        ]);
    }
    public function thongKeThuNhapNhanVien(Request $request)
    {
        $nhanVien = Auth::guard('sanctum')->user();
        if (!$nhanVien) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy nhân viên đang đăng nhập.'], 401);
        }
        $request->validate([
            'tu_ngay' => 'required|date',
            'den_ngay' => 'required|date|after_or_equal:tu_ngay',
        ]);
        $data = DB::table('don_hangs')
            ->whereRaw('JSON_CONTAINS(nhan_vien_id, ?)', [json_encode($nhanVien->id)])
            ->whereDate('created_at', ">=", $request->tu_ngay)
            ->whereDate('created_at', "<=", $request->den_ngay)
            ->selectRaw('DATE(created_at) as ngay_thang, SUM(tong_tien / so_luong_nv) as tong_thu_nhap')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();
        $array_ngay_thang = [];
        $array_tong_thu_nhap = [];
        foreach ($data as $item) {
            $array_ngay_thang[] = $item->ngay_thang;
            $array_tong_thu_nhap[] = $item->tong_thu_nhap;
        }

        return response()->json([
            'status' => true,
            'data' => $data,
            'array_ngay_thang' => $array_ngay_thang,
            'array_tong_thu_nhap' => $array_tong_thu_nhap,
        ]);
    }
}
