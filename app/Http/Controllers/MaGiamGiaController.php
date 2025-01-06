<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaGiamGiaRequest;
use App\Models\MaGiamGia;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MaGiamGiaController extends Controller
{
        public function store(MaGiamGiaRequest $request)
        {
            MaGiamGia::create($request->all());
            return response()->json([
                'status' => true,
                'message' => "Đã tạo mới mã giảm giá " . $request->code . " thành công.",
            ]);
        }
        public function getData()
        {
            $data = MaGiamGia::get();
            return response()->json([
                'data' => $data,
            ]);
        }
    public function update(MaGiamGiaRequest $request, $id)
    {
        $magiamgia = MaGiamGia::find($id);
        if (!$magiamgia) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy mã giảm giá!'
            ], 404);
        }
        $data = [
            'code'                   => $request->code,
            'tinh_trang'             => $request->tinh_trang,
            'ngay_bat_dau'           => $request->ngay_bat_dau,
            'ngay_ket_thuc'          => $request->ngay_ket_thuc,
            'loai_giam_gia'          => $request->loai_giam_gia,
            'so_giam_gia'            => $request->so_giam_gia,
            'so_tien_toi_da'         => $request->so_tien_toi_da,
            'dk_toi_thieu_don_hang'  => $request->dk_toi_thieu_don_hang,
        ];
        $magiamgia->update($data);
        return response()->json([
            'status' => true,
            'message' => "Đã cập nhật mã giảm giá " . $request->code . " thành công.",
        ]);
    }
    public function destroy($id)
    {
        $magiamgia=MaGiamGia::find($id)->delete();
        return response()->json([
            'status' => true,
            'message' => "Đã xóa mã giảm giá thành công.",
        ]);
    }
    public function getDataOpen()
    {
        $data = MaGiamGia::where('tinh_trang', 1)
                         ->whereDate('ngay_bat_dau', "<=", Carbon::today())
                         ->whereDate('ngay_ket_thuc', ">=", Carbon::today())
                         ->get();

        return response()->json([
            'data'      => $data
        ]);
    }
    public function doiTrangThai(Request $request)
    {
        $maGiamGia = MaGiamGia::where('id', $request->id)->first();
        if($maGiamGia) {
            if($maGiamGia->tinh_trang == 0) {
                $maGiamGia->tinh_trang = 1;
            } else {
                $maGiamGia->tinh_trang = 0;
            }
            $maGiamGia->save();

            return response()->json([
                'status'    => true,
                'message'   => "Đã cập nhật trạng thái mã giảm giá thành công!"
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => "Mã giảm giá không tồn tại!"
            ]);
        }
    }
    public function kiemTraMaGiamGia(Request $request)
    {
        $maGiamGia = MaGiamGia::where('code', $request->code)
                         ->whereDate('ngay_bat_dau', "<=", Carbon::today())
                         ->whereDate('ngay_ket_thuc', ">=", Carbon::today())
                         ->where('tinh_trang', 1)
                         ->first();
        if($maGiamGia) {
            return response()->json([
                'status' => true,
                'coupon' => $maGiamGia,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Mã giảm giá không tồn tại trong hệ thống.",
            ]);
        }
    }
}
