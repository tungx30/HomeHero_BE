<?php

namespace App\Http\Controllers;

use App\Http\Requests\LuaChonDichVuRequest;
use App\Models\DanhMucDichVu;
use App\Models\LuaChonDichVu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class LuaChonDichVuController extends Controller
{
    public function getData()
    {
       $data = LuaChonDichVu::get();
       return response()->json([
        'message'    => 'Lấy dữ liệu thành công',
        'data'       => $data,
        ]);
    }
    public function store(LuaChonDichVuRequest $request)
    {
        $danhmuc = LuaChonDichVu::create([
            'ten_lua_chon' => $request->ten_lua_chon,
            'slug_dich_vu' => Str::slug($request->ten_lua_chon),
            'icon_dich_vu' => $request->icon_dich_vu,
            'id_muc' => $request->id_muc,
            'is_active' => $request->is_active,
        ]);

        if (!$danhmuc) {
            return response()->json([
                'status' => false,  
                'message' => 'Thêm dịch vụ thất bại!'
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Đã tạo mới dịch vụ ' . $request->ten_lua_chon . ' thành công.',
        ]);
    }
    public function update(LuaChonDichVuRequest $request , $id)
    {
        $dichvu = LuaChonDichVu::find($id);
        if (!$dichvu) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy dịch vụ!'
            ], 404);
        }
        $data = [
            'ten_lua_chon' => $request->ten_lua_chon,
            'slug_dich_vu' => Str::slug($request->ten_lua_chon),
            'icon_dich_vu' => $request->icon_dich_vu,
            'id_muc' => $request->id_muc,
            'is_active' => $request->is_active,
        ];
        $dichvu->update($data);
        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật thông tin dịch vụ ' . $request->ten_lua_chon . ' thành công.',
        ]);
    }
    public function destroy($id)
    {
        $luachon = LuaChonDichVu::find($id);
       if (!$luachon) {
           return response()->json([
               'status' => false,
               'message' => 'Không tìm thấy lựa chọn!'
           ], 404);
       }
       $luachon->delete();
       return response()->json([
           'status' => true,
           'message' => 'Đã xóa lựa chọn '. $luachon->ten_lua_chon.' thành công.',
       ]);
    }
    public function getSoTien($id_dich_vu)
    {
        $luaChonDichVu = LuaChonDichVu::find($id_dich_vu);
        if (!$luaChonDichVu) {
            return response()->json(['error' => 'Không tìm thấy lựa chọn dịch vụ']);
        }
        $idMuc = $luaChonDichVu->id_muc;
        $danhMucDichVu = DanhMucDichVu::find($idMuc);
        if ($danhMucDichVu) {
            return response()->json(['so_tien' => $danhMucDichVu->so_tien]);
        } else {
            return response()->json(['error' => 'Không tìm thấy danh mục dịch vụ']);
        }
    }

}
