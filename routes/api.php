<?php

use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChiTietLichLamController;
use App\Http\Controllers\DanhGiaNhanVienController;
use App\Http\Controllers\DanhMucDichVuController;
use App\Http\Controllers\DiaChiNguoiDungController;
use App\Http\Controllers\DonHangController;
use App\Http\Controllers\GiaoDichController;
use App\Http\Controllers\GiaoDichNhanVienController;
use App\Http\Controllers\LuaChonDichVuController;
use App\Http\Controllers\MaGiamGiaController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\NganHangNguoiDungController;
use App\Http\Controllers\NganHangNhanVienController;
use App\Http\Controllers\NguoiDungController;
use App\Http\Controllers\NhanVienController;
use App\Http\Controllers\ThongBaoController;
use App\Http\Controllers\ThongKeAdminController;
use App\Http\Controllers\ThongKeNhanVienController;
use App\Http\Controllers\ViDienTuController;
use App\Http\Controllers\ViDienTuNguoiDungController;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::post('/admin/dang-nhap',[AdminController::class,'login']);
Route::post('/admin/dang-xuat',[AdminController::class,'logout']);
Route::post('/admin/dang-xuat-tat-ca',[AdminController::class,'logoutAll']);
Route::post('/admin/quen-mat-khau', [AdminController::class, 'quenMK']);
Route::post('/admin/doi-mat-khau', [AdminController::class, 'doiMK']);
Route::post('/admin/quen-mat-khau/ko-mail', [AdminController::class, 'quenMKNoMail'])->middleware('AdminMiddleware');

Route::get('/admin/getDataProfile',[AdminController::class, 'getDataProfile'])->middleware('AdminMiddleware');
Route::post('/admin/updateProfile',[AdminController::class,'updateProfile'])->middleware('AdminMiddleware');
Route::post('/admin/update-Anh-dai-dien-Profile',[AdminController::class,'changeAnhProfile'])->middleware('AdminMiddleware');
Route::get('/check-admin',[AdminController::class, 'checkAdmin']);

Route::get('/admin/getDataNhanVien',[NhanVienController::class, 'getData'])->middleware('AdminMiddleware');
Route::post('/admin/createNhanVien',[NhanVienController::class,'store'])->middleware('AdminMiddleware');
Route::put('/admin/updateNhanVien/{id}',[NhanVienController::class,'update'])->middleware('AdminMiddleware');
Route::delete('/admin/deleteNhanVien/{id}',[NhanVienController::class,'destroy'])->middleware('AdminMiddleware');
Route::post('/admin/change-Block-NhanVien',[NhanVienController::class,'changeBlock'])->middleware('AdminMiddleware');
Route::post('/admin/change-NhanVien-NoiBat',[NhanVienController::class,'changeNoiBat'])->middleware('AdminMiddleware');
Route::post('/admin/change-NhanVien-FlashSale',[NhanVienController::class,'changeFlashSale'])->middleware('AdminMiddleware');

Route::get('/admin/ma-giam-gia/data', [MaGiamGiaController::class, 'getData'])->middleware('AdminMiddleware');
Route::post('/admin/ma-giam-gia/create', [MaGiamGiaController::class, 'store'])->middleware('AdminMiddleware');
Route::put('/admin/ma-giam-gia/update/{id}', [MaGiamGiaController::class, 'update'])->middleware('AdminMiddleware');
Route::post('/admin/ma-giam-gia/doi-trang-thai', [MaGiamGiaController::class, 'doiTrangThai'])->middleware('AdminMiddleware');
Route::delete('/admin/ma-giam-gia/delete/{id}', [MaGiamGiaController::class, 'destroy'])->middleware('AdminMiddleware');

Route::get('/admin/getDataNguoiDung',[NguoiDungController::class, 'getData'])->middleware('AdminMiddleware');
Route::post('/admin/createNguoiDung',[NguoiDungController::class,'store'])->middleware('AdminMiddleware');
Route::put('/admin/updateNguoiDung/{id}',[NguoiDungController::class,'update'])->middleware('AdminMiddleware');
Route::delete('/admin/deleteNguoiDung/{id}',[NguoiDungController::class,'destroy'])->middleware('AdminMiddleware');
Route::post('/admin/kich-Hoat-TK-Nguoi-dung',[NguoiDungController::class,'kichHoatTaiKhoan'])->middleware('AdminMiddleware');
Route::post('/admin/change-Block-NguoiDung',[NguoiDungController::class,'changeBlock'])->middleware('AdminMiddleware');

Route::get('/admin/getDataDanhMuc',[DanhMucDichVuController::class, 'getData'])->middleware('AdminMiddleware');
Route::post('/admin/createDanhMuc',[DanhMucDichVuController::class,'store'])->middleware('AdminMiddleware');
Route::put('/admin/updateDanhMuc/{id}',[DanhMucDichVuController::class,'update'])->middleware('AdminMiddleware');
Route::delete('/admin/deleteDanhMuc/{id}',[DanhMucDichVuController::class,'destroy'])->middleware('AdminMiddleware');

Route::get('/admin/getDataDichVu',[LuaChonDichVuController::class, 'getData'])->middleware('AdminMiddleware');
Route::post('/admin/createDichVu',[LuaChonDichVuController::class,'store'])->middleware('AdminMiddleware');
Route::put('/admin/updateDichVu/{id}',[LuaChonDichVuController::class,'update'])->middleware('AdminMiddleware');
Route::delete('/admin/deleteDichVu/{id}',[LuaChonDichVuController::class,'destroy'])->middleware('AdminMiddleware');

Route::post('/nhan-vien/dang-nhap',[NhanVienController::class,'login']);
Route::post('/nhan-vien/dang-xuat',[NhanVienController::class,'logout']);
Route::post('/nhan-vien/dang-xuat-tat-ca',[NhanVienController::class,'logoutAll']);
Route::post('/nhan-vien/quen-mat-khau', [NhanVienController::class, 'quenMK']);
Route::post('/nhan-vien/doi-mat-khau', [NhanVienController::class, 'doiMK']);
Route::get('/check-nhanvien',[NhanVienController::class, 'checkNhanVien']);
Route::get('/nhan-vien/getDataProfile',[NhanVienController::class, 'getDataProfile'])->middleware('NhanVienMiddleware');
Route::post('/nhan-vien/updateProfile',[NhanVienController::class,'updateProfile'])->middleware('NhanVienMiddleware');
Route::post('/nhan-vien/update-Anh-dai-dien-Profile',[NhanVienController::class,'changeAnhProfile'])->middleware('NhanVienMiddleware');
Route::post('/nhan-vien/quen-mat-khau/ko-mail', [NhanVienController::class, 'quenMKNoMail'])->middleware('NhanVienMiddleware');

Route::post('/nguoi-dung/dang-ky',[NguoiDungController::class,'register']);
Route::post('/nguoi-dung/kich-hoat', [NguoiDungController::class, 'kichHoat']);
Route::post('/nguoi-dung/dang-nhap',[NguoiDungController::class,'login']);
Route::post('/nguoi-dung/dang-xuat',[NguoiDungController::class,'logout']);
Route::post('/nguoi-dung/quen-mat-khau', [NguoiDungController::class, 'quenMK']);
Route::post('/nguoi-dung/doi-mat-khau', [NguoiDungController::class, 'doiMK']);
Route::post('/nguoi-dung/dang-xuat-tat-ca',[NguoiDungController::class,'logoutAll']);
Route::get('/nguoi-dung/getDataProfile',[NguoiDungController::class, 'getDataProfile'])->middleware('NguoiDungMiddleware');
Route::post('/nguoi-dung/updateProfile',[NguoiDungController::class,'updateProfile'])->middleware('NguoiDungMiddleware');
Route::post('/nguoi-dung/update-Anh-dai-dien-Profile',[NguoiDungController::class,'changeAnhProfile'])->middleware('NguoiDungMiddleware');
Route::post('/nguoi-dung/quen-mat-khau/ko-mail', [NguoiDungController::class, 'quenMKNoMail'])->middleware('NguoiDungMiddleware');

Route::get('/nguoi-dung/get-Data-Nhan-Vien-All',[NhanVienController::class, 'getDataNhanVien']);
Route::get('/nguoi-dung/get-Data-Chi-Tiet-Nhan-Vien/{id}',[NhanVienController::class, 'getDataChiTietNhanVien']);
Route::get('/nguoi-dung/get-Data-Nhan-Vien-Noi-Bat',[NhanVienController::class, 'getDataNhanVienNoiBat']);
Route::get('/nguoi-dung/get-Data-NhanVien-Flash-Sale',[NhanVienController::class, 'getDataNhanVienFlashSale']);
Route::get('/nguoi-dung/get-Data-Dich-Vu',[LuaChonDichVuController::class, 'getData']);
Route::get('/check-nguoi-dung',[NguoiDungController::class, 'checkNguoiDung']);

Route::get('/nguoi-dung/dia-chi-thanh-toan/data/{id_dia_chi}',    [DiaChiNguoiDungController::class, 'getDataTheoID'])->middleware("NguoiDungMiddleware");
Route::get('/nguoi-dung/dia-chi/data',    [DiaChiNguoiDungController::class, 'getData'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/dia-chi/create', [DiaChiNguoiDungController::class, 'store'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/dia-chi/update', [DiaChiNguoiDungController::class, 'update'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/dia-chi/delete', [DiaChiNguoiDungController::class, 'destroy'])->middleware("NguoiDungMiddleware");

Route::post('/ma-giam-gia/kiem-tra',[MaGiamGiaController::class, 'kiemTraMaGiamGia'])->middleware('NguoiDungMiddleware');
Route::get('/ma-giam-gia/data', [MaGiamGiaController::class, 'getDataOpen']);

Route::get('/nguoi-dung/don-hang/{maDonHang}', [DonHangController::class, 'getDonHangByMaDonHang']);
Route::get('/nguoi-dung/lay-lich-lam-ranh-nv/{id_nhan_vien}', [ChiTietLichLamController::class, 'hienLichRanhCuaNhanVien'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/dat-don-hang-voi-nhan-vien/create/{id_nhan_vien}', [DonHangController::class, 'storeNhanVien'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/dat-don-hang/create/{id_dich_vu}', [DonHangController::class, 'store'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/huy-don-hang/delete/{id_don_hang}', [DonHangController::class, 'destroy'])->middleware("NguoiDungMiddleware");
Route::get('/nguoi-dung/chi-tiet-don-hang/getData/{id_don_hang}', [DonHangController::class, 'getDonHangChiTiet'])->middleware("NguoiDungMiddleware");
Route::get('/nguoi-dung/so-tien-mac-dinh/{id_dich_vu}', [LuaChonDichVuController::class, 'getSoTien'])->middleware("NguoiDungMiddleware");
Route::get('/nguoi-dung/lich-su-don-hang-da-hoan-thanh/getDataLSHTToND', [DonHangController::class, 'getLSDHHoanThanh'])->middleware("NguoiDungMiddleware");
Route::get('/nguoi-dung/lich-su-don-hang-da-nhan-don/getDataLSNDToND', [DonHangController::class, 'getLSDHDaNhan'])->middleware("NguoiDungMiddleware");
Route::get('/nguoi-dung/lich-su-don-hang-da-huy-don/getDataLSHDToND', [DonHangController::class, 'getLSDHDaHuy'])->middleware("NguoiDungMiddleware");
Route::get('/nguoi-dung/lich-su-don-hang-moi-dat/getDataLSDDToND', [DonHangController::class, 'getLSDHMoiDat'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/thay-doi-status-don-hang/changeStaTusHoanThanh/{id_don_hang}', [DonHangController::class, 'changeStatus'])->middleware("NguoiDungMiddleware");

Route::post('/nguoi-dung/danh-gia/create/{id_don_hang}', [DanhGiaNhanVienController::class, 'store'])->middleware("NguoiDungMiddleware");
Route::get('/nguoi-dung/lay-danh-gia-nhan-vien/data/{id_nhan_vien}', [DanhGiaNhanVienController::class, 'getData']);
Route::get('/nguoi-dung/dem-danh-gia/{id_nhan_vien}', [DanhGiaNhanVienController::class, 'countDanhGia']);
Route::post('/nhan-vien/danh-gia/data', [DanhGiaNhanVienController::class, 'getDataNV'])->middleware("NhanVienMiddleware");

Route::get('/nhan-vien/don-hang/getDataDon/{id_don_hang}', [DonHangController::class, 'getDataDonNV'])->middleware("NhanVienMiddleware");
Route::get('/nhan-vien/don-hang/getAllDataDon', [DonHangController::class, 'getAllDataDonNV'])->middleware("NhanVienMiddleware");
Route::post('/nhan-vien/don-hang/nhanDon', [DonHangController::class, 'nhanDonNV'])->middleware("NhanVienMiddleware");
Route::get('/nhan-vien/lich-lam/getDataLichLam', [ChiTietLichLamController::class, 'getData'])->middleware("NhanVienMiddleware");

Route::get('/nguoi-dung/thong-bao/getNhanDonTuNV', [ThongBaoController::class, 'getDataNhanDonTuNhanVien'])->middleware("NguoiDungMiddleware");
Route::get('/nhan-vien/thong-bao/getNhanDonTuND', [ThongBaoController::class, 'getDataNhanDonTuNguoiDung'])->middleware("NhanVienMiddleware");

Route::get('/nguoi-dung/giao-dich-thanh-toan', [GiaoDichController::class, 'index'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/vi-dien-tu/napTien', [GiaoDichController::class, 'napTienNguoiDung'])->middleware("NguoiDungMiddleware");
Route::get('/nguoi-dung/vi-dien-tu/getData', [ViDienTuNguoiDungController::class, 'getData'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/vi-dien-tu/guiYeuCauRutTien', [ThongBaoController::class, 'taoThongBaoRutTienChoNguoiDung'])->middleware("NguoiDungMiddleware");
Route::get('/nguoi-dung/getData-giao-dich', [GiaoDichController::class, 'getData'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/search-giao-dich', [GiaoDichController::class, 'search'])->middleware("NguoiDungMiddleware");

Route::post('/nhan-vien/vi-dien-tu/tien-don-dv', [ViDienTuController::class, 'tinhLuong'])->middleware("NhanVienMiddleware");
Route::get('/nhan-vien/vi-dien-tu/getData', [ViDienTuController::class, 'getData'])->middleware("NhanVienMiddleware");
Route::post('/nhan-vien/vi-dien-tu/napTien', [GiaoDichNhanVienController::class, 'napTienNhanVien'])->middleware("NhanVienMiddleware");
Route::post('/nhan-vien/vi-dien-tu/guiYeuCauRutTien', [ThongBaoController::class, 'taoThongBaoRutTienChoNhanVien'])->middleware("NhanVienMiddleware");
Route::get('/nhan-vien/getData-giao-dich', [GiaoDichNhanVienController::class, 'getData'])->middleware("NhanVienMiddleware");
Route::post('/nhan-vien/search-giao-dich', [GiaoDichNhanVienController::class, 'search'])->middleware("NhanVienMiddleware");

Route::get('/admin/getData-Yeu-Cau-Rut-Tien-NV-ND', [ThongBaoController::class, 'getDataRutTienNV'])->middleware("AdminMiddleware");
Route::get('/admin/getData-thanh-toan-yeu-cau-rut-tien-NV/{id_thong_bao}', [ViDienTuController::class, 'getthanhToanRutTienNV'])->middleware("AdminMiddleware");
Route::get('/admin/getData-thanh-toan-yeu-cau-rut-tien-ND/{id_thong_bao}', [ViDienTuController::class, 'getthanhToanRutTienND'])->middleware("AdminMiddleware");
Route::post('/admin/vi-dien-tu/nhan-vien-rut-Tien/{id_thong_bao}', [GiaoDichNhanVienController::class, 'rutTienNhanVien'])->middleware("AdminMiddleware");
Route::post('/admin/vi-dien-tu/nguoi-dung-rut-Tien/{id_thong_bao}', [GiaoDichController::class, 'rutTienNguoiDung'])->middleware("AdminMiddleware");

Route::get('/admin/giao-dich-nhan-vien', [GiaoDichNhanVienController::class, 'getDataNV'])->middleware("AdminMiddleware");
Route::get('/admin/giao-dich-nguoi-dung', [GiaoDichController::class, 'getDataND'])->middleware("AdminMiddleware");
Route::post('/admin/search-giao-dich-nhan-vien', [GiaoDichNhanVienController::class, 'searchNV'])->middleware("AdminMiddleware");
Route::post('/admin/search-giao-dich-nguoi-dung', [GiaoDichController::class, 'searchND'])->middleware("AdminMiddleware");

Route::post('/admin/gui-tin-nhan', [MessagesController::class, 'sendMessageByAdmin'])->middleware("AdminMiddleware");
Route::post('/admin/lich-su-nhan-tin-nhan', [MessagesController::class, 'getMessageByAdmin'])->middleware("AdminMiddleware");
Route::post('/admin/chi-tiet-tin-nhan', [MessagesController::class, 'getChiTietTinNhan'])->middleware("AdminMiddleware");
Route::get('/admin/id', function (Request $request) {
    $admin = Auth::guard('sanctum')->user();
    return response()->json(['admin_id' => $admin->id]);
});
Route::get('/admin/info', function (Request $request) {
    // Lấy thông tin admin đầu tiên trong bảng (dựa vào ID hoặc thứ tự tạo)
    $admin = Admin::first(); // Lấy admin đầu tiên
    if (!$admin) {
        return response()->json(['error' => 'Không có admin nào tồn tại'], 404);
    }
    return response()->json([
        'admin_id' => $admin->id,
        'ho_va_ten' => $admin->ho_va_ten,
        'email' => $admin->email,
        'hinh_anh' => $admin->hinh_anh,
        'so_dien_thoai' => $admin->so_dien_thoai,
        'tinh_trang' => $admin->tinh_trang
    ]);
})->middleware("NguoiDungMiddleware");
Route::get('/nguoi-dung/id', function (Request $request) {
    // Lấy thông tin người dùng đầu tiên trong bảng (dựa vào ID hoặc thứ tự tạo)
    $nguoiDung = Auth::guard('sanctum')->user();
    return response()->json(['nguoi_dung_id' => $nguoiDung->id]);
})->middleware("NguoiDungMiddleware");

Route::get('/nhan-vien/id', function (Request $request) {
    // Lấy thông tin người dùng đầu tiên trong bảng (dựa vào ID hoặc thứ tự tạo)
    $nhanVien = Auth::guard('sanctum')->user();
    return response()->json(['nhan_vien_id' => $nhanVien->id]);
})->middleware("NhanVienMiddleware");

Route::post('/nguoi-dung/gui-tin-nhan', [MessagesController::class, 'sendMessageByUser'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/lich-su-nhan-tin-nhan', [MessagesController::class, 'getMessageByUser'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/chi-tiet-tin-nhan', [MessagesController::class, 'getChiTietTinNhanNguoiDung'])->middleware("NguoiDungMiddleware");

Route::post('/nguoi-dung/ngan-hang-vi/create', [NganHangNguoiDungController::class, 'store'])->middleware("NguoiDungMiddleware");
Route::post('/nguoi-dung/ngan-hang-vi/check', [NganHangNguoiDungController::class, 'checkTK'])->middleware("NguoiDungMiddleware");

Route::get('/admin/ngan-hang-vi-nguoi-dung/getData', [NganHangNguoiDungController::class, 'getData'])->middleware("AdminMiddleware");
Route::post('/admin/ngan-hang-vi-nguoi-dung/updateQr', [NganHangNguoiDungController::class, 'updateQR'])->middleware("AdminMiddleware");
Route::post('/admin/ngan-hang-vi-nguoi-dung/congTien', [NganHangNguoiDungController::class, 'congTien'])->middleware("AdminMiddleware");
Route::post('/admin/ngan-hang-vi-nguoi-dung/truTien', [NganHangNguoiDungController::class, 'truTien'])->middleware("AdminMiddleware");

Route::post('/nhan-vien/ngan-hang-vi/check', [NganHangNhanVienController::class, 'checkTK'])->middleware("NhanVienMiddleware");
Route::get('/admin/ngan-hang-vi-nguoi-dung/getDataAll', [NganHangNhanVienController::class, 'getDataAll'])->middleware("AdminMiddleware");
Route::post('/admin/ngan-hang-vi-nhan-vien/create/{id_nhan_vien}', [NganHangNhanVienController::class, 'store'])->middleware("AdminMiddleware");
Route::get('/admin/ngan-hang-vi-nhan-vien/getData/{id_nhan_vien}', [NganHangNhanVienController::class, 'getData'])->middleware("AdminMiddleware");
Route::put('/admin/ngan-hang-vi-nhan-vien/update/{id_ngan_hang}', [NganHangNhanVienController::class, 'update'])->middleware("AdminMiddleware");
Route::post('/admin/ngan-hang-vi-nhan-vien/congTien', [NganHangNhanVienController::class, 'congTien'])->middleware("AdminMiddleware");
Route::post('/admin/ngan-hang-vi-nhan-vien/truTien', [NganHangNhanVienController::class, 'truTien'])->middleware("AdminMiddleware");

Route::get('/admin/don-hang/getDataAll', [DonHangController::class, 'getDataAll'])->middleware("AdminMiddleware");
Route::post('/admin/don-hang/search', [DonHangController::class, 'search'])->middleware("AdminMiddleware");

Route::get('/nhan-vien/thong-ke/so-luong-cv', [ThongKeNhanVienController::class, 'countSoLuongCV'])->middleware("NhanVienMiddleware");
Route::get('/nhan-vien/thong-ke/so-gio-lam-viec', [ThongKeNhanVienController::class, 'sumSoGioLamViec'])->middleware("NhanVienMiddleware");
Route::get('/nhan-vien/thong-ke/thu-nhap-hien-tai', [ThongKeNhanVienController::class, 'sumThuNhap'])->middleware("NhanVienMiddleware");
Route::get('/nhan-vien/thong-ke/danh-gia-tu-khach-hang', [ThongKeNhanVienController::class, 'getTongSoSao'])->middleware("NhanVienMiddleware");
Route::post('/nhan-vien/thong-ke/thong-ke-thu-nhap', [ThongKeNhanVienController::class, 'thongKeThuNhapNhanVien'])->middleware("NhanVienMiddleware");

Route::get('/admin/thong-ke/so-luong-khach-hang', [ThongKeAdminController::class, 'tongSoKhachHang'])->middleware("AdminMiddleware");
Route::get('/admin/thong-ke/so-luong-danh-gia', [ThongKeAdminController::class, 'tongSoDanhGia'])->middleware("AdminMiddleware");
Route::get('/admin/thong-ke/so-luong-dat-lich', [ThongKeAdminController::class, 'tongSoDatLich'])->middleware("AdminMiddleware");
Route::post('/admin/thong-ke/thong-ke-thu-nhap-he-thong', [ThongKeAdminController::class, 'thongKeThuNhapHeThong'])->middleware("AdminMiddleware");

Route::post('/admin/thay-doi-trang-thai-doc/{id_thong_bao}', [ThongBaoController::class, 'changeIsRead'])->middleware("AdminMiddleware");
Route::post('/nguoi-dung/thay-doi-trang-thai-doc/{id_thong_bao}', [ThongBaoController::class, 'changeIsRead'])->middleware("NguoiDungMiddleware");
Route::post('/nhan-vien/thay-doi-trang-thai-doc/{id_thong_bao}', [ThongBaoController::class, 'changeIsRead'])->middleware("NhanVienMiddleware");

Route::post('/chat', [ChatbotController::class, 'chat']);
