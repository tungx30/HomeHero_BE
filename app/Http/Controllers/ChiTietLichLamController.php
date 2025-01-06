<?php

namespace App\Http\Controllers;

use App\Models\ChiTietLichLam;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ChiTietLichLamController extends Controller
{
    public function getData(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $nhanVien = Auth::guard('sanctum')->user();
        $data = ChiTietLichLam::where('chi_tiet_lich_lams.nhan_vien_id', $nhanVien->id)
            ->join('don_hangs', 'chi_tiet_lich_lams.don_hang_id', '=', 'don_hangs.id')
            ->join('nguoi_dungs', 'don_hangs.nguoi_dung_id', '=', 'nguoi_dungs.id')
            ->join('dia_chi_nguoi_dungs', 'don_hangs.id_dia_chi', '=', 'dia_chi_nguoi_dungs.id')
            ->join('lua_chon_dich_vus', 'don_hangs.id_dich_vu', '=', 'lua_chon_dich_vus.id')
            ->select(
                'chi_tiet_lich_lams.*',
                'don_hangs.ma_don_hang',
                'nguoi_dungs.ho_va_ten as ten_nguoi_nhan',
                'nguoi_dungs.so_dien_thoai as so_dien_thoai_nguoi_nhan',
                'dia_chi_nguoi_dungs.dia_chi as dia_chi_khach_hang',
                'lua_chon_dich_vus.ten_lua_chon as ten_dich_vu'
            )
            ->whereBetween('chi_tiet_lich_lams.ngay_lam_viec', [$startDate, $endDate])
            ->get();

        if ($data->isNotEmpty()) {
            return response()->json([
                'status' => true,
                'data' => $data,
                'message' => 'Lấy chi tiết lịch làm thành công.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Không có chi tiết lịch làm nào.'
            ]);
        }
    }
    public function layLichRanhCuaNhanVien($nhanVienId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? Carbon::today();
        $endDate = $endDate ?? Carbon::today()->addDays(7);

        $gioBatDauLamViec = Carbon::createFromTime(8, 0, 0);
        $gioKetThucLamViec = Carbon::createFromTime(21, 0, 0);
        $lichRach = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $daySchedule = [];
            $bookedSchedules = ChiTietLichLam::where('nhan_vien_id', $nhanVienId)
                ->whereDate('ngay_lam_viec', $date->toDateString())
                ->get();
            $freeTimes = [
                [
                    'batDau' => $gioBatDauLamViec->copy(),
                    'ketThuc' => $gioKetThucLamViec->copy(),
                ],
            ];
            // Loại bỏ các khoảng thời gian đã được đặt
            foreach ($bookedSchedules as $schedule) {
                $scheduleStart = Carbon::parse($schedule->gio_bat_dau);
                $scheduleEnd = Carbon::parse($schedule->gio_ket_thuc);
                foreach ($freeTimes as $key => $freeTime) {
                    // Nếu khoảng thời gian đặt nằm trong khoảng thời gian trống
                    if (
                        $scheduleStart->between($freeTime['batDau'], $freeTime['ketThuc']) ||
                        $scheduleEnd->between($freeTime['batDau'], $freeTime['ketThuc']) ||
                        ($scheduleStart->lte($freeTime['batDau']) && $scheduleEnd->gte($freeTime['ketThuc']))
                    ) {
                        // Loại bỏ khoảng thời gian trống hiện tại
                        unset($freeTimes[$key]);
                        // Tạo các khoảng thời gian trống mới nếu có
                        if ($freeTime['batDau']->lt($scheduleStart)) {
                            $freeTimes[] = [
                                'batDau' => $freeTime['batDau']->copy(),
                                'ketThuc' => $scheduleStart->copy(),
                            ];
                        }
                        if ($scheduleEnd->lt($freeTime['ketThuc'])) {
                            $freeTimes[] = [
                                'batDau' => $scheduleEnd->copy(),
                                'ketThuc' => $freeTime['ketThuc']->copy(),
                            ];
                        }
                    }
                }
                // Sắp xếp lại mảng freeTimes theo thời gian bắt đầu
                usort($freeTimes, function ($a, $b) {
                    return $a['batDau']->timestamp - $b['batDau']->timestamp;
                });
            }
            // Lưu các khoảng thời gian trống trong ngày
            if (!empty($freeTimes)) {
                foreach ($freeTimes as $freeTime) {
                    $daySchedule[] = [
                        'batDau' => $freeTime['batDau']->format('H:i'),
                        'ketThuc' => $freeTime['ketThuc']->format('H:i'),
                    ];
                }
            }
            // Nếu có lịch trống trong ngày, thêm vào kết quả
            if (!empty($daySchedule)) {
                $lichRach[] = [
                    'ngayRanh' => $date->toDateString(),
                    'thoiGianRanh' => $daySchedule,
                ];
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Lấy lịch trống thành công',
            'data' => $lichRach,
        ]);
    }
    public function hienLichRanhCuaNhanVien( $nhanVienId)
    {
        return $this->layLichRanhCuaNhanVien($nhanVienId);
    }
}
