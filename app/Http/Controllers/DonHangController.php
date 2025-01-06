<?php

namespace App\Http\Controllers;

use App\Http\Requests\DonHangRequest;
use App\Models\ChiTietLichLam;
use App\Models\DiaChiNguoiDung;
use App\Models\DonHang;
use App\Models\GiaoDich;
use App\Models\LuaChonDichVu;
use App\Models\NhanVien;
use App\Models\ViDienTu;
use App\Models\ViDienTuNguoiDung;
use Illuminate\Console\View\Components\Info;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonHangController extends Controller
{
    public function store(Request $request, $id_dich_vu)
    {
        $khachHang = Auth::guard('sanctum')->user();
        $diaChi = DiaChiNguoiDung::where('id', $request->id_dia_chi)
            ->where('id_nguoi_dung', $khachHang->id)
            ->first();
        $luachon = LuaChonDichVu::select('lua_chon_dich_vus.*', 'danh_muc_dich_vus.so_tien')
            ->join('danh_muc_dich_vus', 'lua_chon_dich_vus.id_muc', '=', 'danh_muc_dich_vus.id')
            ->where('lua_chon_dich_vus.id', $id_dich_vu)
            ->first();

        if (!$diaChi) {
            return response()->json(['status' => false, 'message' => "Địa chỉ chưa được chọn"]);
        } elseif (!$request->filled('phuong_thuc_thanh_toan')) {
            return response()->json(['status' => false, 'message' => "Phương thức thanh toán chưa được chọn"]);
        }

        if (!$request->filled('gio_bat_dau_lam_viec')) {
            return response()->json(['status' => false, 'message' => "Giờ bắt đầu làm việc chưa được truyền vào"]);
        }

        $gioBatDau = Carbon::parse($request->gio_bat_dau_lam_viec);
        $ngayLamViec = $request->ngay_bat_dau_lam ?? Carbon::today()->toDateString();

        // Kiểm tra đơn hàng đã tồn tại với cùng ngày, giờ và trạng thái (1 hoặc 2)
        $existingOrder = DonHang::where('ngay_bat_dau_lam', $ngayLamViec)
            ->where('gio_bat_dau_lam_viec', $gioBatDau->format('H:i:s'))
            ->whereIn('tinh_trang_don_hang', [1, 2])
            ->where('id_dia_chi', $request->id_dia_chi)
            ->first();

        if ($existingOrder) {
            return response()->json(['status' => false, 'message' => "Đã tồn tại đơn hàng vào thời gian và ngày này. Vui lòng chọn thời gian khác hoặc địa chỉ khác."]);
        }
        // $tongTien = 0;
        $soGioPhucVu = $request->so_gio_phuc_vu ?? 4;
        $soNhanVien = $request->so_luong_nv ?? 1;
        // Tính giờ kết thúc
        $calculateEndTime = function ($startTime, $workingHours) {
            return (clone $startTime)->addHours(floor($workingHours))
                ->addMinutes(($workingHours - floor($workingHours)) * 60);
        };

        // Kiểm tra giờ bắt đầu và kết thúc hợp lệ
        if ($gioBatDau->hour < 8) {
            return response()->json(['status' => false, 'message' => "Giờ bắt đầu không được nhỏ hơn 8h sáng"]);
        }

        // Tính giờ kết thúc
        $gioKetThuc = $calculateEndTime($gioBatDau, $soGioPhucVu);
        if ($gioKetThuc->hour > 21 || ($gioKetThuc->hour == 21 && $gioKetThuc->minute > 0)) {
            return response()->json(['status' => false, 'message' => "Giờ kết thúc không được vượt quá 21h tối nên vui lòng chọn lại giờ bắt đầu"]);
        }
        $ngayPhucVuHangTuan = $request->so_ngay_phuc_vu_hang_tuan;
        $soBuoiHangThang = is_array($ngayPhucVuHangTuan) ? count($ngayPhucVuHangTuan) * 4 : 0;
        $tongSoBuoi = $soBuoiHangThang * ($request->so_thang_phuc_vu ?? 1);
        $gioBatDauFormatted = $gioBatDau->format('H:i:s');
        $gioKetThucFormatted = $gioKetThuc->format('H:i:s');
        if ($request->phuong_thuc_thanh_toan == 2) {
            // Lấy ví điện tử của người dùng
            $viNguoiDung = ViDienTuNguoiDung::where('nguoi_dung_id', $khachHang->id)->first();

            if (!$viNguoiDung) {
                // Nếu người dùng chưa có ví điện tử, trả về thông báo lỗi
                return response()->json([
                    'status' => false,
                    'message' => 'Người dùng chưa có ví điện tử. Vui lòng tạo ví trước khi thanh toán.',
                ]);
            }

            $soDuVi = $viNguoiDung->so_du;
            $soTienThanhToan = $request->so_tien_thanh_toan;

            if ($soDuVi < $soTienThanhToan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Số dư ví không đủ để thanh toán. Vui lòng chọn phương thức thanh toán khác.',
                ]);
            }

            // Trừ tiền từ ví người dùng
            $viNguoiDung->so_du -= $soTienThanhToan;
            $viNguoiDung->save();
        }
        $donHang = DonHang::create([
            'ma_don_hang' => 'Chưa có',
            'id_dich_vu' => $luachon->id,
            'nhan_vien_id' => $request->nhan_vien_id ?? null,
            'nguoi_dung_id' => $khachHang->id,
            'id_dia_chi' => $diaChi->id,
            'so_luong_nv' => $soNhanVien,
            'so_tang_phuc_vu' => $request->so_tang_phuc_vu ?? null,
            'so_gio_phuc_vu' => $soGioPhucVu,
            'gio_bat_dau_lam_viec' => $gioBatDauFormatted,
            'gio_ket_thuc_lam_viec' => $gioKetThucFormatted,
            'so_ngay_phuc_vu_hang_tuan' => json_encode($request->so_ngay_phuc_vu_hang_tuan),
            'ngay_bat_dau_lam' => $request->ngay_bat_dau_lam ?? Carbon::today()->toDateString(),
            'tong_so_buoi_phuc_vu_theo_so_thang_phuc_vu' => $tongSoBuoi ?? null,
            'so_thang_phuc_vu' => $request->so_thang_phuc_vu ?? null,
            'loai_nha' => $request->loai_nha ?? null,
            'dien_tich_tong_san' => $request->dien_tich_tong_san,
            'tong_tien' => $request->tong_tien,
            'ma_code_giam' => $request->ma_code_giam ?? null,
            'so_tien_giam' => $request->so_tien_giam ?? 0,
            'so_tien_thanh_toan' => $request->so_tien_thanh_toan,
            'is_thanh_toan' => $request->phuong_thuc_thanh_toan == 2 ? 1 : 0,
            'tinh_trang_don_hang' => 1,
            'ghi_chu' => $request->ghi_chu ?? null,
            'phuong_thuc_thanh_toan' => $request->phuong_thuc_thanh_toan,
            'is_da_tinh_luong' => 0,
        ]);

        $donHang->ma_don_hang = 'HomeHero308' . $donHang->id;
        $donHang->save();

        // Kiểm tra và tạo lịch làm dựa trên phương thức thanh toán
        $this->kiemTraTienVaTaoLich($donHang, $khachHang, $request, $gioBatDauFormatted, $gioKetThucFormatted, $soGioPhucVu, $tongSoBuoi);
        // Gửi thông báo cho nhân viên
        $loiNhan = "Người dùng " . $khachHang->ho_va_ten . " đã đặt đơn hàng mới với mã đơn dịch vụ " . $donHang->ma_don_hang . ". Hãy bấm xem chi tiết đơn để xem thông tin chi tiết đơn";
        $this->taoThongBaoChoNhanVienNhanDon($loiNhan, $khachHang->id, $donHang->id);
        return response()->json(['status' => true, 'message' => 'Đơn dịch vụ đã được đặt thành công. Hãy đợi nhân viên nhận đơn này', 'donHang' => $donHang]);
    }

    private function kiemTraTienVaTaoLich($donHang, $khachHang, $request, $gioBatDauFormatted, $gioKetThucFormatted, $soGioPhucVu, $tongSoBuoi)
    {
        $existingScheduleEntries = ChiTietLichLam::where('don_hang_id', $donHang->id)->count();

        if ($existingScheduleEntries == 0) {
            switch ($donHang->phuong_thuc_thanh_toan) {
                case 1:
                    $this->taoLichLam($donHang, $khachHang, $request, $gioBatDauFormatted, $gioKetThucFormatted, $soGioPhucVu, $tongSoBuoi);
                    break;

                case 2:
                    if ($donHang->is_thanh_toan == 1) {
                        $this->taoLichLam($donHang, $khachHang, $request, $gioBatDauFormatted, $gioKetThucFormatted, $soGioPhucVu, $tongSoBuoi);
                    }
                    break;
                case 0:
                    $this->taoLichLam($donHang, $khachHang, $request, $gioBatDauFormatted, $gioKetThucFormatted, $soGioPhucVu, $tongSoBuoi);
                    break;
                default:
                    break;
            }
        }
    }
    private function taoLichLam($donHang, $khachHang, $request, $gioBatDauFormatted, $gioKetThucFormatted, $soGioPhucVu, $tongSoBuoi)
    {
        // Tạo lịch làm dựa trên loại dịch vụ
        if ($donHang->id_dich_vu == 2) { // Dịch vụ định kỳ
            $startDate = Carbon::parse($donHang->ngay_bat_dau_lam ?? Carbon::now())->startOfDay();
            $currentDate = $startDate->copy();
            $soBuoiDaTao = 0;
            $ngayPhucVuList = [];
            $gioBatDauRequest = Carbon::parse($donHang->gio_bat_dau);

            while ($soBuoiDaTao < $tongSoBuoi) {
                foreach ($request->so_ngay_phuc_vu_hang_tuan as $ngay) {
                    if ($currentDate->dayOfWeek == $ngay) {
                        $ngayPhucVu = $currentDate->copy();
                    } elseif ($currentDate->dayOfWeek < $ngay) {
                        $ngayPhucVu = $currentDate->copy()->next($ngay)->subWeek();
                    } else {
                        $ngayPhucVu = $currentDate->copy()->next($ngay);
                    }

                    if ($ngayPhucVu->isToday() && $gioBatDauRequest->lessThan(Carbon::now())) {
                        continue;
                    }

                    if ($soBuoiDaTao < $tongSoBuoi && $ngayPhucVu->greaterThanOrEqualTo($startDate)) {
                        $ngayPhucVuList[] = $ngayPhucVu;
                        $soBuoiDaTao++;
                    }
                }
                $currentDate->addWeek();
            }

            sort($ngayPhucVuList);

            foreach ($ngayPhucVuList as $ngayPhucVu) {
                ChiTietLichLam::create([
                    'nhan_vien_id' => $request->nhan_vien_id ?? null,
                    'don_hang_id' => $donHang->id,
                    'ngay_lam_viec' => $ngayPhucVu,
                    'so_gio_phuc_vu' => $soGioPhucVu,
                    'gio_bat_dau' => $gioBatDauFormatted,
                    'gio_ket_thuc' => $gioKetThucFormatted,
                ]);
            }
        } else { // Thuê theo giờ hoặc tổng vệ sinh
            ChiTietLichLam::create([
                'nhan_vien_id' => $request->nhan_vien_id ?? null,
                'don_hang_id' => $donHang->id,
                'ngay_lam_viec' => $donHang->ngay_bat_dau_lam,
                'so_gio_phuc_vu' => $soGioPhucVu,
                'gio_bat_dau' => $gioBatDauFormatted,
                'gio_ket_thuc' => $gioKetThucFormatted,
            ]);
        }
    }
    public function storeNhanVien(Request $request, $nhanVienId)
    {
        $khachHang = Auth::guard('sanctum')->user();

        // Kiểm tra sự tồn tại của địa chỉ và phương thức thanh toán
        $diaChi = DiaChiNguoiDung::where('id', $request->id_dia_chi)
            ->where('id_nguoi_dung', $khachHang->id)
            ->first();

        if (!$diaChi) {
            return response()->json(['status' => false, 'message' => "Địa chỉ chưa được chọn"]);
        } elseif (!$request->filled('phuong_thuc_thanh_toan')) {
            return response()->json(['status' => false, 'message' => "Phương thức thanh toán chưa được chọn"]);
        }

        // Kiểm tra giờ bắt đầu làm việc
        if (!$request->filled('gio_bat_dau_lam_viec')) {
            return response()->json(['status' => false, 'message' => "Giờ bắt đầu làm việc chưa được truyền vào"]);
        }

        $gioBatDau = Carbon::parse($request->gio_bat_dau_lam_viec);
        $ngayLamViec = $request->ngay_bat_dau_lam ?? Carbon::today()->toDateString();

        // Kiểm tra giờ bắt đầu và kết thúc hợp lệ
        if ($gioBatDau->hour < 8) {
            return response()->json(['status' => false, 'message' => "Giờ bắt đầu không được nhỏ hơn 8h sáng"]);
        }

        $soGioPhucVu = $request->so_gio_phuc_vu ?? 4;

        // Tính giờ kết thúc
        $gioKetThuc = $gioBatDau->copy()->addHours($soGioPhucVu);

        if ($gioKetThuc->hour > 21 || ($gioKetThuc->hour == 21 && $gioKetThuc->minute > 0)) {
            return response()->json(['status' => false, 'message' => "Giờ kết thúc không được vượt quá 21h tối, vui lòng chọn lại giờ bắt đầu"]);
        }

        $gioBatDauFormatted = $gioBatDau->format('H:i:s');
        $gioKetThucFormatted = $gioKetThuc->format('H:i:s');

        // Kiểm tra lịch trùng của nhân viên
        $lichLamTrung = ChiTietLichLam::where('nhan_vien_id', $nhanVienId)
            ->where('ngay_lam_viec', $ngayLamViec)
            ->where(function ($query) use ($gioBatDauFormatted, $gioKetThucFormatted) {
                $query->whereBetween('gio_bat_dau', [$gioBatDauFormatted, $gioKetThucFormatted])
                    ->orWhereBetween('gio_ket_thuc', [$gioBatDauFormatted, $gioKetThucFormatted])
                    ->orWhere(function ($subQuery) use ($gioBatDauFormatted, $gioKetThucFormatted) {
                        $subQuery->where('gio_bat_dau', '<=', $gioBatDauFormatted)
                            ->where('gio_ket_thuc', '>=', $gioKetThucFormatted);
                    });
            })
            ->exists();

        if ($lichLamTrung) {
            return response()->json(['status' => false, 'message' => 'Nhân viên đã có lịch làm trùng ngày và giờ. Vui lòng chọn thời gian khác.']);
        }
        if ($request->phuong_thuc_thanh_toan == 2) {
            // Lấy ví điện tử của người dùng
            $viNguoiDung = ViDienTuNguoiDung::where('nguoi_dung_id', $khachHang->id)->first();

            if (!$viNguoiDung) {
                // Nếu người dùng chưa có ví điện tử, trả về thông báo lỗi
                return response()->json([
                    'status' => false,
                    'message' => 'Người dùng chưa có ví điện tử. Vui lòng tạo ví trước khi thanh toán.',
                ]);
            }

            $soDuVi = $viNguoiDung->so_du;
            $soTienThanhToan = $request->so_tien_thanh_toan;

            if ($soDuVi < $soTienThanhToan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Số dư ví không đủ để thanh toán. Vui lòng chọn phương thức thanh toán khác.',
                ]);
            }

            // Trừ tiền từ ví người dùng
            $viNguoiDung->so_du -= $soTienThanhToan;
            $viNguoiDung->save();
        }
        // Tạo đơn hàng
        $donHang = DonHang::create([
            'ma_don_hang' => 'Chưa có',
            'id_dich_vu' => 1, // Dịch vụ theo giờ
            'nhan_vien_id' => null, // Sẽ xử lý như JSON
            'nguoi_dung_id' => $khachHang->id,
            'id_dia_chi' => $diaChi->id,
            'so_luong_nv' => 1,
            'so_gio_phuc_vu' => $soGioPhucVu,
            'gio_bat_dau_lam_viec' => $gioBatDauFormatted,
            'gio_ket_thuc_lam_viec' => $gioKetThucFormatted,
            'ngay_bat_dau_lam' => $ngayLamViec,
            'tong_tien' => $request->tong_tien,
            'ma_code_giam' => $request->ma_code_giam ?? null,
            'so_tien_giam' => $request->so_tien_giam ?? 0,
            'phuong_thuc_thanh_toan' => $request->phuong_thuc_thanh_toan,
            'tinh_trang_don_hang' => 2,
            'tong_tien' => $request->tong_tien,
            'so_tien_thanh_toan' => $request->so_tien_thanh_toan,
            'is_thanh_toan' => $request->phuong_thuc_thanh_toan == 1 ? 0 : 0,
            'ghi_chu' => $request->ghi_chu ?? null,
        ]);

        $nhanVienIds = json_decode($donHang->nhan_vien_id, true) ?? [];
        if (!in_array($nhanVienId, $nhanVienIds)) {
            $nhanVienIds[] = $nhanVienId;
            $donHang->nhan_vien_id = json_encode($nhanVienIds);
            $donHang->save();
        }

        $donHang->ma_don_hang = 'HomeHero308' . $donHang->id;
        $donHang->save();

        // Tạo chi tiết lịch làm
        ChiTietLichLam::create([
            'nhan_vien_id' => $nhanVienId,
            'don_hang_id' => $donHang->id,
            'ngay_lam_viec' => $ngayLamViec,
            'so_gio_phuc_vu' => $soGioPhucVu,
            'gio_bat_dau' => $gioBatDauFormatted,
            'gio_ket_thuc' => $gioKetThucFormatted,
        ]);

        if ($request->phuong_thuc_thanh_toan == 1) {
            $loiNhan = "Người dùng " . $khachHang->ho_va_ten . " đã đặt đơn hàng mới với mã đơn dịch vụ " . $donHang->ma_don_hang . ". Hãy bấm xem chi tiết đơn để xem thông tin chi tiết đơn";
            $this->taoThongBaoChoNhanVienNhanDon($loiNhan, $khachHang->id, $donHang->id);
        }
        return response()->json(['status' => true, 'message' => 'Đặt lịch với nhân viên thành công !!!', 'donHang' => $donHang]);
    }

    public function destroy(Request $request, $id_don_hang)
    {
        $khachHang = Auth::guard('sanctum')->user();
        $donHang = DonHang::where('id', $id_don_hang)
            ->where('nguoi_dung_id', $khachHang->id)
            ->first();
        if (!$donHang) {
            return response()->json([
                'status' => false,
                'message' => 'Đơn hàng không tồn tại.'
            ]);
        }
        if ($donHang->is_thanh_toan == 1) {
            return response()->json([
                'status' => false,
                'message' => 'Đơn dịch vụ đã được thanh toán, bạn không thể hủy đơn dịch vụ này được.'
            ]);
        }
        if ($donHang->tinh_trang_don_hang != 1 || !is_null($donHang->nhan_vien_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể hủy đơn hàng bởi vì đơn hàng đã được nhận bởi nhân viên hoặc không ở trạng thái có thể hủy. Hãy liên hệ với quản trị viên để hủy đơn hàng nếu bạn muốn.'
            ]);
        }
        ChiTietLichLam::where('don_hang_id', $donHang->id)->delete();
        $donHang->tinh_trang_don_hang = 4;
        $donHang->save();
        if ($donHang) {
            $loiNhan = "Người dùng " . $khachHang->ho_va_ten . " đã hủy đơn dịch vụ với mã đơn dịch vụ " . $donHang->ma_don_hang . ". Vui lòng nhận đơn dịch vụ khác ";
            $this->taoThongBaoChoNhanVienNhanDon($loiNhan, $khachHang->id, $donHang->id);
        }
        return response()->json([
            'status' => true,
            'message' => 'Đã hủy đơn hàng thành công.'
        ]);
    }
    public function getDonHangChiTiet($id_don_hang)
    {
        $khachHang = Auth::guard('sanctum')->user();
        $donHang = DB::table('don_hangs')
            ->join('dia_chi_nguoi_dungs', 'don_hangs.id_dia_chi', '=', 'dia_chi_nguoi_dungs.id')
            ->where('don_hangs.id', $id_don_hang)
            ->where('don_hangs.nguoi_dung_id', $khachHang->id)
            ->select(
                'don_hangs.*',
                'dia_chi_nguoi_dungs.dia_chi as dia_chi_nguoi_nhan',
                'dia_chi_nguoi_dungs.so_dien_thoai as so_dien_thoai_nguoi_nhan',
                'dia_chi_nguoi_dungs.ten_nguoi_nhan as ten_nguoi_nhan',
                'don_hangs.id_dich_vu', // Thêm loại dịch vụ để xử lý logic
                'don_hangs.so_thang_phuc_vu', // Số tháng phục vụ
                'don_hangs.so_ngay_phuc_vu_hang_tuan' // Số ngày phục vụ hàng tuần
            )
            ->first();

        if (!$donHang) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy đơn hàng.'
            ], 404);
        }

        // Giải mã nhan_vien_id từ JSON
        $nhanVienIds = $donHang->nhan_vien_id ? json_decode($donHang->nhan_vien_id, true) : [];

        // Lấy thông tin nhân viên nếu có id
        $nhanViens = [];
        if (!empty($nhanVienIds)) {
            $nhanViens = DB::table('nhan_viens')
                ->whereIn('id', $nhanVienIds)
                ->select(
                    'id',
                    'ho_va_ten as ten_nhan_vien',
                    'so_dien_thoai as so_dien_thoai_nhan_vien',
                    'email as email_nhan_vien',
                    'kinh_nghiem as kinh_nghiem_nhan_vien'
                )
                ->get();
        }
        $donHang->nhanViens = $nhanViens;
        $donHang->ngay_ket_thuc = $this->getNgayKetThuc($donHang->id, $donHang->id_dich_vu, $donHang->ngay_bat_dau_lam);
        return response()->json([
            'status' => true,
            'data' => $donHang,
            'message' => 'Lấy chi tiết đơn hàng thành công.'
        ]);
    }

    public function getLSDHHoanThanh(Request $request)
    {
        $khachHang = Auth::guard('sanctum')->user();

        $donHangs = DB::table('don_hangs')
            ->join('dia_chi_nguoi_dungs', 'don_hangs.id_dia_chi', '=', 'dia_chi_nguoi_dungs.id')
            ->where('don_hangs.nguoi_dung_id', $khachHang->id)
            ->where('don_hangs.tinh_trang_don_hang', 3)
            ->select(
                'don_hangs.*',
                'dia_chi_nguoi_dungs.dia_chi as dia_chi_nguoi_nhan',
                'dia_chi_nguoi_dungs.so_dien_thoai as so_dien_thoai_nguoi_nhan',
                'dia_chi_nguoi_dungs.ten_nguoi_nhan as ten_nguoi_nhan'
            )
            ->get();

        foreach ($donHangs as $donHang) {
            $nhanVienIds = $donHang->nhan_vien_id ? json_decode($donHang->nhan_vien_id, true) : [];
            $donHang->nhanViens = $nhanVienIds ? DB::table('nhan_viens')
                ->whereIn('id', $nhanVienIds)
                ->select('id', 'ho_va_ten as ten_nhan_vien', 'so_dien_thoai', 'email', 'kinh_nghiem')
                ->get() : [];

            // Xử lý ngày kết thúc
            $donHang->ngay_ket_thuc = $this->getNgayKetThuc($donHang->id, $donHang->id_dich_vu, $donHang->ngay_bat_dau_lam);
        }

        return response()->json([
            'status' => true,
            'data' => $donHangs,
            'message' => 'Lấy lịch sử đơn hàng hoàn thành thành công.'
        ]);
    }
    public function getLSDHDaNhan(Request $request)
    {
        $khachHang = Auth::guard('sanctum')->user();

        $donHangs = DB::table('don_hangs')
            ->join('dia_chi_nguoi_dungs', 'don_hangs.id_dia_chi', '=', 'dia_chi_nguoi_dungs.id')
            ->where('don_hangs.nguoi_dung_id', $khachHang->id)
            ->where('don_hangs.tinh_trang_don_hang', 2)
            ->select(
                'don_hangs.*',
                'dia_chi_nguoi_dungs.dia_chi as dia_chi_nguoi_nhan',
                'dia_chi_nguoi_dungs.so_dien_thoai as so_dien_thoai_nguoi_nhan',
                'dia_chi_nguoi_dungs.ten_nguoi_nhan as ten_nguoi_nhan'
            )
            ->get();

        foreach ($donHangs as $donHang) {
            $nhanVienIds = $donHang->nhan_vien_id ? json_decode($donHang->nhan_vien_id, true) : [];
            $donHang->nhanViens = $nhanVienIds ? DB::table('nhan_viens')
                ->whereIn('id', $nhanVienIds)
                ->select('id', 'ho_va_ten as ten_nhan_vien', 'so_dien_thoai', 'email', 'kinh_nghiem')
                ->get() : [];

            // Xử lý ngày kết thúc
            $donHang->ngay_ket_thuc = $this->getNgayKetThuc($donHang->id, $donHang->id_dich_vu, $donHang->ngay_bat_dau_lam);
        }

        return response()->json([
            'status' => true,
            'data' => $donHangs,
            'message' => 'Lấy lịch sử đơn hàng đã nhận thành công.'
        ]);
    }

    public function getLSDHMoiDat()
    {
        $khachHang = Auth::guard('sanctum')->user();
        $donHangs = DB::table('don_hangs')
            ->where('nguoi_dung_id', $khachHang->id)
            ->where('tinh_trang_don_hang', 1)
            ->get();
        foreach ($donHangs as $donHang) {
            $donHang->ngay_ket_thuc = $this->getNgayKetThuc($donHang->id, $donHang->id_dich_vu, $donHang->ngay_bat_dau_lam);
        }

        return response()->json([
            'status' => true,
            'data' => $donHangs,
            'message' => 'Lấy lịch sử các đơn hàng vừa đặt thành công.'
        ]);
    }
    public function getLSDHDaHuy(Request $request)
    {
        $khachHang = Auth::guard('sanctum')->user();

        $donHangs = DB::table('don_hangs')
            ->where('nguoi_dung_id', $khachHang->id)
            ->where('tinh_trang_don_hang', 4)
            ->get();

        foreach ($donHangs as $donHang) {
            $donHang->ngay_ket_thuc = $this->getNgayKetThuc($donHang->id, $donHang->id_dich_vu, $donHang->ngay_bat_dau_lam);
        }

        return response()->json([
            'status' => true,
            'data' => $donHangs,
            'message' => 'Lấy lịch sử đơn hàng đã hủy thành công.'
        ]);
    }
    public function getDataDonNV($id_don_hang)
    {
        $data = DonHang::where('don_hangs.id', $id_don_hang)
            ->join('nguoi_dungs', 'don_hangs.nguoi_dung_id', '=', 'nguoi_dungs.id')
            ->join('dia_chi_nguoi_dungs', 'nguoi_dungs.id', '=', 'dia_chi_nguoi_dungs.id_nguoi_dung')
            ->select(
                'don_hangs.*',
                'nguoi_dungs.ho_va_ten as ten_nguoi_dat',
                'nguoi_dungs.so_dien_thoai as so_dien_thoai_nguoi_dat',
                'dia_chi_nguoi_dungs.dia_chi as dia_chi_khach_hang',
                'dia_chi_nguoi_dungs.ten_nguoi_nhan as ten_nguoi_su_dung_dich_vu',
                'dia_chi_nguoi_dungs.so_dien_thoai as so_dien_thoai_nguoi_su_dung_dich_vu'
            )
            ->first();

        if ($data) {
            // Kiểm tra điều kiện thanh toán
            if (
                in_array($data->phuong_thuc_thanh_toan, [0, 2]) && // Thanh toán online hoặc qua ví
                $data->is_thanh_toan != 1 // Chưa thanh toán
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng này cần được thanh toán trước khi nhận.'
                ]);
            }
            $data->ngay_ket_thuc = $this->getNgayKetThuc($data->id, $data->id_dich_vu, $data->ngay_bat_dau_lam);
            // Trường hợp thanh toán bằng tiền mặt hoặc các điều kiện hợp lệ
            return response()->json([
                'status' => true,
                'data' => $data,
                'message' => 'Lấy chi tiết đơn hàng thành công.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Đơn hàng không tồn tại.'
            ]);
        }
    }
    private function getNgayKetThuc($donHangId, $loaiDichVu, $ngayBatDau)
    {
        if ($loaiDichVu === 2) {
            $lastSchedule = DB::table('chi_tiet_lich_lams')
                ->where('don_hang_id', $donHangId)
                ->orderBy('ngay_lam_viec', 'desc')
                ->first();

            return $lastSchedule ? $lastSchedule->ngay_lam_viec : $ngayBatDau;
        }
        return $ngayBatDau;
    }

    public function getAllDataDonNV()
    {
        $nhanVien = Auth::guard('sanctum')->user();
        if (!$nhanVien) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy thông tin nhân viên.'
            ], 404);
        }

        $data = DonHang::whereRaw('JSON_CONTAINS(nhan_vien_id, ?)', [$nhanVien->id])
            ->whereIn('tinh_trang_don_hang', [2, 3])
            ->join('nguoi_dungs', 'don_hangs.nguoi_dung_id', '=', 'nguoi_dungs.id')
            ->join('dia_chi_nguoi_dungs', 'don_hangs.id_dia_chi', '=', 'dia_chi_nguoi_dungs.id')
            ->join('lua_chon_dich_vus', 'don_hangs.id_dich_vu', '=', 'lua_chon_dich_vus.id')
            ->select(
                'don_hangs.*',
                'don_hangs.id as id_don_hang',
                'nguoi_dungs.ho_va_ten as ten_nguoi_dat',
                'nguoi_dungs.so_dien_thoai as so_dien_thoai_nguoi_dat',
                'dia_chi_nguoi_dungs.dia_chi as dia_chi_khach_hang',
                'dia_chi_nguoi_dungs.ten_nguoi_nhan as ten_nguoi_su_dung_dich_vu',
                'dia_chi_nguoi_dungs.so_dien_thoai as so_dien_thoai_nguoi_su_dung_dich_vu',
                'lua_chon_dich_vus.ten_lua_chon as ten_lua_chon_dich_vu'
            )
            ->get();

        // Lặp qua từng đơn hàng và thêm ngày kết thúc
        $data = $data->map(function ($donHang) {
            $donHang->ngay_ket_thuc = $this->getNgayKetThuc(
                $donHang->id_don_hang,
                $donHang->id_dich_vu,
                $donHang->ngay_bat_dau_lam
            );
            return $donHang;
        });

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Lấy lịch sử đơn hàng cho nhân viên mà đã nhận thành công.'
        ]);
    }

    public function nhanDonNV(Request $request)
    {
        $nhanVien = Auth::guard('sanctum')->user();
        $donHangId = $request->input('id');
        $donHang = DonHang::find($donHangId);

        if (!$donHang) {
            return response()->json(['status' => false, 'message' => 'Đơn hàng không tồn tại.']);
        }

        // Kiểm tra trạng thái đơn hàng
        if ($donHang->tinh_trang_don_hang != 1) {
            return response()->json(['status' => false, 'message' => 'Đơn hàng này không còn ở trạng thái có thể nhận.']);
        }

        // Kiểm tra nhân viên đã nhận đơn này chưa
        $lichLamTonTai = ChiTietLichLam::where('don_hang_id', $donHang->id)
            ->where('nhan_vien_id', $nhanVien->id)
            ->exists();
        if ($lichLamTonTai) {
            return response()->json(['status' => false, 'message' => 'Bạn đã nhận đơn hàng này rồi.']);
        }

        $viDienTu = ViDienTu::where('nhan_vien_id', $nhanVien->id)->first();
        $soDuHienTai = $viDienTu->so_du;

        // Kiểm tra số dư ví điện tử
        if ($soDuHienTai < 500000) {
            return response()->json(['status' => false, 'message' => 'Số dư trong ví điện tử không đủ để nhận đơn. Vui lòng nạp thêm tiền để nhận đơn']);
        }
        if ($donHang->phuong_thuc_thanh_toan == 1) {
            $tongTienDonHang = $donHang->tong_tien;
            if ($soDuHienTai < $tongTienDonHang) {
                return response()->json(['status' => false, 'message' => 'Số dư trong ví đang thấp hơn số tiền lương ở đơn dịch vụ nên không thể nhận đơn hàng này. Vui lòng nạp thêm tiền để nhận đơn']);
            }
        }

        // Kiểm tra số lượng nhân viên đã nhận đơn
        $soNhanVienDaNhan = ChiTietLichLam::where('don_hang_id', $donHang->id)
            ->where('is_nhan_lich', 1)
            ->distinct('nhan_vien_id')
            ->count();
        if ($soNhanVienDaNhan >= $donHang->so_luong_nv) {
            return response()->json([
                'status' => false,
                'message' => 'Đơn hàng này đã được nhận đủ số lượng nhân viên yêu cầu.'
            ]);
        }

        // Lấy giờ bắt đầu và kết thúc công việc cho đơn hàng
        $gioBatDauDonHang = Carbon::parse($donHang->gio_bat_dau_lam_viec);
        $gioKetThucDonHang = Carbon::parse($donHang->gio_ket_thuc_lam_viec);

        // Kiểm tra trùng lặp lịch làm việc cho nhân viên
        // Nếu đơn hàng định kỳ nhiều ngày, bạn cần lặp qua các ngày trong chi tiết lịch làm.
        // Ở đây, ví dụ đơn hàng chỉ có 1 ngày (ngay_bat_dau_lam).
        $lichLamTrung = ChiTietLichLam::where('nhan_vien_id', $nhanVien->id)
            ->where('ngay_lam_viec', $donHang->ngay_bat_dau_lam)
            ->where(function ($query) use ($gioBatDauDonHang, $gioKetThucDonHang) {
                $query->where(function ($subQuery) use ($gioBatDauDonHang, $gioKetThucDonHang) {
                    $subQuery->whereBetween('gio_bat_dau', [$gioBatDauDonHang, $gioKetThucDonHang])
                        ->orWhereBetween('gio_ket_thuc', [$gioBatDauDonHang, $gioKetThucDonHang]);
                })
                    ->orWhere(function ($subQuery) use ($gioBatDauDonHang, $gioKetThucDonHang) {
                        $subQuery->where('gio_bat_dau', '<=', $gioBatDauDonHang)
                            ->where('gio_ket_thuc', '>=', $gioKetThucDonHang);
                    });
            })
            ->exists();

        if ($lichLamTrung) {
            return response()->json(['status' => false, 'message' => 'Bạn đã có lịch làm trùng ngày và giờ với một đơn hàng khác.']);
        }

        // Cập nhật lịch làm việc
        $chiTietLichLamList = ChiTietLichLam::where('don_hang_id', $donHang->id)
            ->where('is_nhan_lich', 0)
            ->get();

        if ($chiTietLichLamList->isEmpty()) {
            // Nếu không có dòng nào chưa nhận, tạo mới
            ChiTietLichLam::create([
                'nhan_vien_id' => $nhanVien->id,
                'don_hang_id' => $donHang->id,
                'ngay_lam_viec' => $donHang->ngay_bat_dau_lam,
                'so_gio_phuc_vu' => $donHang->so_gio_phuc_vu,
                'gio_bat_dau' => $donHang->gio_bat_dau_lam_viec,
                'gio_ket_thuc' => $donHang->gio_ket_thuc_lam_viec,
                'is_nhan_lich' => 1
            ]);
        } else {
            // Cập nhật tất cả các bản ghi
            foreach ($chiTietLichLamList as $item) {
                $item->update([
                    'nhan_vien_id' => $nhanVien->id,
                    'is_nhan_lich' => 1
                ]);
            }
        }

        // Cập nhật danh sách nhân viên đã nhận đơn
        $nhanVienIds = json_decode($donHang->nhan_vien_id, true) ?? [];
        if (!in_array($nhanVien->id, $nhanVienIds)) {
            $nhanVienIds[] = $nhanVien->id;
            $donHang->nhan_vien_id = json_encode($nhanVienIds);
        }

        // Kiểm tra nếu đủ nhân viên thì cập nhật trạng thái đơn hàng
        $soNhanVienDaNhan = ChiTietLichLam::where('don_hang_id', $donHang->id)
            ->where('is_nhan_lich', 1)
            ->distinct('nhan_vien_id')
            ->count();
        if ($soNhanVienDaNhan >= $donHang->so_luong_nv) {
            $donHang->tinh_trang_don_hang = 2; // Đã nhận đủ nhân viên
        }
        $donHang->save();

        // Gửi thông báo cho người dùng
        $loiNhan = "Nhân viên " . $nhanVien->ho_va_ten . " đã nhận đơn dịch vụ của bạn với mã đơn dịch vụ " . $donHang->ma_don_hang . ". Hãy bấm xem chi tiết đơn để xem thông tin chi tiết đơn.";
        $this->taoThongBaoChoNguoiDungDatDon($loiNhan, $nhanVien->id, $donHang->nguoi_dung_id, $donHang->id);

        return response()->json(['status' => true, 'message' => 'Đơn hàng đã được nhận thành công', 'donHang' => $donHang]);
    }
    public function getDonHangByMaDonHang($maDonHang)
    {
        $khachHang = Auth::guard('sanctum')->user();
        $donHang = DonHang::where('ma_don_hang', $maDonHang)
            ->where('nguoi_dung_id', $khachHang->id)
            ->first();

        if ($donHang) {
            return response()->json(['status' => true, 'donHang' => $donHang]);
        } else {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy đơn hàng.']);
        }
    }
    public function changeStatus($id_don_hang)
    {
        $nguoiDung = Auth::guard('sanctum')->user();
        $donHang = DonHang::where('id', $id_don_hang)
            ->where('nguoi_dung_id', $nguoiDung->id)
            ->first();
        if (!$donHang) {
            return response()->json(['status' => false, 'message' => 'Đơn hàng không tồn tại hoặc không thuộc về bạn']);
        }
        if ($donHang->tinh_trang_don_hang == 2) {
            $donHang->tinh_trang_don_hang = 3;
            $this->tinhLuongChoNhanVien($donHang);
            $donHang->save();
        }
        ChiTietLichLam::where('don_hang_id', $donHang->id)->delete(); // Đã hoàn thành
        return response()->json(['status' => true, 'message' => 'Cập nhật trạng thái đơn hàng và chi tiết lịch làm thành công']);
    }
    private function tinhLuongChoNhanVien($donHang)
    {
        $nhanVienIds = json_decode($donHang->nhan_vien_id, true);
        foreach ($nhanVienIds as $nhanVienId) {
            $nhanVien = NhanVien::find($nhanVienId);
            if (!$nhanVien) continue;
            $viDienTu = ViDienTu::where('nhan_vien_id', $nhanVien->id)->first();
            if (!$viDienTu) continue;
            $isDaTinhLuong = json_decode($donHang->is_da_tinh_luong, true);
            // Khởi tạo mảng nếu null
            if (!is_array($isDaTinhLuong)) {
                $isDaTinhLuong = [];
            }
            // Tính tiền lương cho nhân viên
            $soTienGoc = $donHang->tong_tien / $donHang->so_luong_nv;
            $soTienGoc = round($soTienGoc, 2);
            if ($donHang->phuong_thuc_thanh_toan == 0 && $donHang->is_thanh_toan == 1) {
                // Thanh toán trực tuyến (80% cho nhân viên)
                $soTienNhanVien = $soTienGoc * 0.8;
                $viDienTu->so_du += $soTienNhanVien;
            } elseif ($donHang->phuong_thuc_thanh_toan == 1) {
                // Thanh toán tiền mặt (trừ 20% số tiền từ ví nhân viên)
                $soTienNhanVien = $soTienGoc * 0.2;
                $viDienTu->so_du -= $soTienNhanVien;
            } elseif ($donHang->phuong_thuc_thanh_toan == 2 && $donHang->is_thanh_toan == 1) {
                // Thanh toán qua ví điện tử (80% cho nhân viên)
                $soTienNhanVien = $soTienGoc * 0.8;
                $viDienTu->so_du += $soTienNhanVien;
            } else {
                continue;
            }
            $viDienTu->save();
            $isDaTinhLuong[$nhanVien->id] = 1;

            log::info('Mảng is_da_tinh_luong sau khi gán: ' . json_encode($isDaTinhLuong));
            $donHang->is_da_tinh_luong = json_encode($isDaTinhLuong);
            $donHang->save();
        }
    }

    public function getDataAll()
    {
        $donHangs = DonHang::leftJoin('dia_chi_nguoi_dungs', 'don_hangs.id_dia_chi', '=', 'dia_chi_nguoi_dungs.id')
            ->leftJoin('lua_chon_dich_vus', 'don_hangs.id_dich_vu', '=', 'lua_chon_dich_vus.id')
            ->select(
                'don_hangs.*',
                'dia_chi_nguoi_dungs.ten_nguoi_nhan',
                'dia_chi_nguoi_dungs.so_dien_thoai',
                'lua_chon_dich_vus.ten_lua_chon'
            )
            ->get();

        foreach ($donHangs as $donHang) {
            // Giải mã nhan_vien_id từ JSON
            $nhanVienIds = json_decode($donHang->nhan_vien_id, true);

            // Kiểm tra nếu nhan_vien_id hợp lệ
            if (is_array($nhanVienIds) && !empty($nhanVienIds)) {
                // Lấy thông tin nhân viên dựa trên các ID
                $nhanVien = NhanVien::whereIn('id', $nhanVienIds)->get(['id', 'ho_va_ten']);
                $donHang->nhan_vien = $nhanVien; // Gắn thông tin nhân viên vào đơn hàng
            } else {
                $donHang->nhan_vien = null; // Nếu không có nhân viên, gán null
            }
        }

        return response()->json([
            'status' => true,
            'data' => $donHangs
        ]);
    }
    public function search(Request $request)
    {
        $noi_dung_tim = '%' . $request->noi_dung_tim . '%';

        // Tìm kiếm và join vào các bảng liên quan
        $donHangs = DonHang::leftJoin('dia_chi_nguoi_dungs', 'don_hangs.id_dia_chi', '=', 'dia_chi_nguoi_dungs.id')
            ->leftJoin('lua_chon_dich_vus', 'don_hangs.id_dich_vu', '=', 'lua_chon_dich_vus.id')
            ->select(
                'don_hangs.*',
                'dia_chi_nguoi_dungs.ten_nguoi_nhan',
                'dia_chi_nguoi_dungs.so_dien_thoai',
                'lua_chon_dich_vus.ten_lua_chon'
            )
            ->where('don_hangs.ma_don_hang', 'like', $noi_dung_tim)
            ->orWhere('lua_chon_dich_vus.ten_lua_chon', 'like', $noi_dung_tim)
            ->orWhere('dia_chi_nguoi_dungs.ten_nguoi_nhan', 'like', $noi_dung_tim)
            ->get();

        // Lặp qua từng đơn hàng để xử lý thông tin nhân viên
        foreach ($donHangs as $donHang) {
            // Giải mã nhan_vien_id từ JSON
            $nhanVienIds = json_decode($donHang->nhan_vien_id, true);

            if (is_array($nhanVienIds) && !empty($nhanVienIds)) {
                // Lấy danh sách nhân viên theo ID
                $nhanVien = NhanVien::whereIn('id', $nhanVienIds)->get(['id', 'ho_va_ten']);

                // Gắn danh sách tên nhân viên vào đơn hàng
                $donHang->nhan_vien = $nhanVien->pluck('ho_va_ten')->toArray();
            } else {
                $donHang->nhan_vien = null; // Nếu không có nhân viên, gán null
            }
        }

        return response()->json([
            'status' => true,
            'data' => $donHangs,
        ]);
    }
}
