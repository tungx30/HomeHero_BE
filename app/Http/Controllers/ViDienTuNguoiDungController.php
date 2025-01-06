<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\ViDienTuNguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViDienTuNguoiDungController extends Controller
{
    public function getData()
    {
          $nguoiDung = Auth::guard('sanctum')->user();
          if (!$nguoiDung) {
              return response()->json([
                  'message' => 'Không tìm thấy thông tin người dùng từ token.'
              ], 404);
          }
          $data = ViDienTuNguoiDung::join('nguoi_dungs', 'vi_dien_tu_nguoi_dungs.nguoi_dung_id', '=', 'nguoi_dungs.id')
              ->where('vi_dien_tu_nguoi_dungs.nguoi_dung_id', $nguoiDung->id)
              ->select('vi_dien_tu_nguoi_dungs.*', 'nguoi_dungs.*') // Lấy toàn bộ thông tin từ cả hai bảng
              ->first();

          if ($data) {
              return response()->json([
                  'message' => 'Lấy dữ liệu thành công.',
                  'data' => $data,
              ], 200);
          }

          return response()->json([
              'message' => 'Không tìm thấy dữ liệu về ví điện tử hoặc người dùng.'
          ], 404);
    }
}
