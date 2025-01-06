<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDanhMucDichVuRequest;
use App\Models\DanhMucDichVu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class DanhMucDichVuController extends Controller
{
    public function getData()
    {
        $data= DanhMucDichVu::get();
        return response()->json([
            'status'     => true,
            'message'    => 'Lấy dữ liệu thành công',
            'data'       => $data,
        ]);
    }
    public function store(StoreDanhMucDichVuRequest $request)
    {
        $danhmuc = DanhMucDichVu::create([
            'ten_muc' => $request->ten_muc,
            'slug_ten_muc' => Str::slug($request->ten_muc),
            'so_tien' => $request->so_tien,
            'is_active' => $request->is_active,
        ]);

        if (!$danhmuc) {
            return response()->json([
                'status' => false,
                'message' => 'Thêm danh mục thất bại!'
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Đã tạo mới danh mục ' . $request->ten_muc . ' thành công.',
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(StoreDanhMucDichVuRequest $request , $id)
    {

         $danhmuc = DanhMucDichVu::find($id);
         if (!$danhmuc) {
             return response()->json([
                 'status' => false,
                 'message' => 'Không tìm thấy danh mục!'
             ], 404);
         }
         $data = [
            'ten_muc' => $request->ten_muc,
            'slug_ten_muc' => Str::slug($request->ten_muc),
            'so_tien' => $request->so_tien,
            'is_active' => $request->is_active,
         ];
         // Cập nhật thông tin nhân viên
         $danhmuc->update($data);

         return response()->json([
             'status' => true,
             'message' => 'Đã cập nhật thông tin danh mục ' . $request->ten_muc . ' thành công.',
         ]);
    }
    public function destroy($id)
    {
        $danhmuc = DanhMucDichVu::find($id);
       if (!$danhmuc) {
           return response()->json([
               'status' => false,
               'message' => 'Không tìm thấy danh mục!'
           ], 404);
       }
       $danhmuc->delete();
       return response()->json([
           'status' => true,
           'message' => 'Đã xóa danh mục '. $danhmuc->ten_muc.' thành công.',
       ]);
    }
}
