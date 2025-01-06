<?php

namespace App\Http\Controllers;

use App\Models\DanhGiaNhanVien;
use App\Models\DonHang;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ThongKeAdminController extends Controller
{
    public function tongSoKhachHang()
    {
        // Lấy thời gian hiện tại
        $thangHienTai = Carbon::now()->format('Y-m');
        $thangTruoc = Carbon::now()->subMonth()->format('Y-m');

        // Tổng số khách hàng trong tháng hiện tại
        $tongKhachHangThangHienTai = NguoiDung::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        // Tổng số khách hàng trong tháng trước
        $tongKhachHangThangTruoc = NguoiDung::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();

        // Tổng số khách hàng từ trước đến nay
        $tongKhachHangToanBo = NguoiDung::count();

        // Tính phần trăm tăng/giảm
        if ($tongKhachHangThangTruoc > 0) {
            $tyLeThayDoi = (($tongKhachHangThangHienTai - $tongKhachHangThangTruoc) / $tongKhachHangThangTruoc) * 100;
        } else {
            // Nếu tháng trước không có khách hàng nào, coi như tăng 100%
            $tyLeThayDoi = $tongKhachHangThangHienTai > 0 ? 100 : 0;
        }

        return response()->json([
            'tong_khach_hang_thang_hien_tai' => $tongKhachHangThangHienTai,
            'tong_khach_hang_thang_truoc' => $tongKhachHangThangTruoc,
            'tong_khach_hang_toan_bo' => $tongKhachHangToanBo,
            'ty_le_thay_doi' => round($tyLeThayDoi, 2),
        ]);
    }
    public function tongSoDanhGia()
    {
        // Lấy thời gian hiện tại
        $currentMonth = Carbon::now()->format('Y-m');
        $previousMonth = Carbon::now()->subMonth()->format('Y-m');

        // Tổng số đánh giá trong tháng hiện tại
        $currentMonthCount = DanhGiaNhanVien::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        // Tổng số đánh giá trong tháng trước
        $previousMonthCount = DanhGiaNhanVien::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();

        // Tổng số đánh giá từ trước đến nay
        $totalCount = DanhGiaNhanVien::count();

        // Tính phần trăm thay đổi
        if ($previousMonthCount > 0) {
            $percentageChange = (($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100;
        } else {
            // Nếu tháng trước không có đánh giá, coi như tăng 100%
            $percentageChange = $currentMonthCount > 0 ? 100 : 0;
        }

        return response()->json([
            'tong_danh_gia_thang_hien_tai' => $currentMonthCount,
            'tong_danh_gia_thang_truoc' => $previousMonthCount,
            'tong_danh_gia_toan_bo' => $totalCount,
            'ty_le_thay_doi' => round($percentageChange, 2),
        ]);
    }
    public function tongSoDatLich()
    {
        // Lấy thời gian hiện tại
        $currentMonth = Carbon::now()->format('Y-m');
        $previousMonth = Carbon::now()->subMonth()->format('Y-m');

        // Tổng số đặt lịch trong tháng hiện tại
        $currentMonthCount = DonHang::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        // Tổng số đặt lịch trong tháng trước
        $previousMonthCount = DonHang::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();

        // Tổng số đặt lịch từ trước đến nay
        $totalCount = DonHang::count();

        // Tính phần trăm thay đổi
        if ($previousMonthCount > 0) {
            $percentageChange = (($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100;
        } else {
            // Nếu tháng trước không có đặt lịch, coi như tăng 100%
            $percentageChange = $currentMonthCount > 0 ? 100 : 0;
        }

        return response()->json([
            'tong_dat_lich_thang_hien_tai' => $currentMonthCount,
            'tong_dat_lich_thang_truoc' => $previousMonthCount,
            'tong_dat_lich_toan_bo' => $totalCount,
            'ty_le_thay_doi' => round($percentageChange, 2),
        ]);
    }
    public function thongKeThuNhapHeThong(Request $request)
{
    // Xác thực đầu vào
    $request->validate([
        'tu_ngay' => 'required|date',
        'den_ngay' => 'required|date|after_or_equal:tu_ngay',
    ]);

    // Lấy dữ liệu thống kê
    $data = DB::table('don_hangs')
        ->whereDate('created_at', ">=", $request->tu_ngay)
        ->whereDate('created_at', "<=", $request->den_ngay)
        ->selectRaw('DATE(created_at) as ngay_thang, SUM(tong_tien) as tong_thu_nhap')
        ->groupBy(DB::raw('DATE(created_at)'))
        ->get();

    // Tạo mảng để trả về theo yêu cầu
    $array_ngay_thang = [];
    $array_tong_thu_nhap = [];
    foreach ($data as $item) {
        $array_ngay_thang[] = $item->ngay_thang;
        $array_tong_thu_nhap[] = $item->tong_thu_nhap;
    }

    // Trả về kết quả
    return response()->json([
        'status' => true,
        'data' => $data,
        'array_ngay_thang' => $array_ngay_thang,
        'array_tong_thu_nhap' => $array_tong_thu_nhap,
    ]);
}

}
