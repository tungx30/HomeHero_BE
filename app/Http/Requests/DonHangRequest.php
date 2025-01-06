<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DonHangRequest extends FormRequest
{
    /**
     * Xác định người dùng có được phép thực hiện request này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Lấy các quy tắc xác thực được áp dụng cho request này.
     */
    public function rules(): array
    {
        return [
            'ma_don_hang' => 'required|string|unique:don_hangs,ma_don_hang',
            'id_dich_vu' => 'required|exists:lua_chon_dich_vus,id',
            'nhan_vien_id' => 'nullable|exists:nhan_viens,id',
            'nguoi_dung_id' => 'exists:nguoi_dungs,id',
            'id_dia_chi' => 'required|exists:dia_chi_nguoi_dungs,id',
            'so_luong_nv' => 'required|integer|min:1',
            'so_tang_phuc_vu' => 'nullable|integer|min:1',
            'so_gio_phuc_vu' => 'nullable|numeric|min:0.5|max:24',
            'gio_bat_dau_lam_viec' => 'nullable|date', // có thể chuyển thành 'datetime' nếu dùng theo giờ
            'gio_ket_thuc_lam_viec' => 'nullable|date|after_or_equal:gio_bat_dau_lam_viec', // có thể chuyển thành 'datetime'
            'ngay_phuc_vu_hang_tuan' => 'nullable|integer|min:0|max:6',
            'so_buoi_phuc_vu_hang_thang' => 'nullable|integer|min:1',
            'so_thang_phuc_vu' => 'nullable|integer|min:1',
            'loai_nha' => 'nullable|string|in:1,2,3,4',
            'dien_tich_tong_san' => 'nullable|numeric|min:1|max:3.5',
            'tong_tien' => 'required|integer|min:0',
            'ma_code_giam' => 'nullable|string',
            'so_tien_giam' => 'nullable|integer|min:0',
            'so_tien_thanh_toan' => 'required|integer|min:0',
            'is_thanh_toan' => 'nullable|integer|in:0,1', // thêm nullable
            'tinh_trang_don_hang' => 'nullable|integer|min:0|max:5', // thêm nullable
            'ghi_chu' => 'nullable|string',
            'phuong_thuc_thanh_toan' => 'required|integer|in:0,1',
        ];
    }

    /**
     * Lấy các thông báo lỗi tùy chỉnh cho các quy tắc xác thực.
     */
    public function messages(): array
    {
        return [
            'ma_don_hang.required' => 'Mã đơn hàng là bắt buộc.',
            'ma_don_hang.unique' => 'Mã đơn hàng đã tồn tại.',
            'id_dich_vu.required' => 'Dịch vụ là bắt buộc.',
            'id_dich_vu.exists' => 'Dịch vụ không tồn tại.',
            'nhan_vien_id.exists' => 'Nhân viên không tồn tại.',
            'nguoi_dung_id.exists' => 'Người dùng không tồn tại.',
            'id_dia_chi.required' => 'Địa chỉ là bắt buộc.',
            'id_dia_chi.exists' => 'Địa chỉ không tồn tại.',
            'so_luong_nv.required' => 'Số lượng nhân viên là bắt buộc.',
            'so_luong_nv.integer' => 'Số lượng nhân viên phải là số nguyên.',
            'tong_tien.required' => 'Tổng tiền là bắt buộc.',
            'so_tien_thanh_toan.required' => 'Số tiền thanh toán là bắt buộc.',
            'is_thanh_toan.in' => 'Trạng thái thanh toán không hợp lệ.',
            'tinh_trang_don_hang.required' => 'Tình trạng đơn hàng là bắt buộc.',
            'tinh_trang_don_hang.in' => 'Tình trạng đơn hàng không hợp lệ.',
            'phuong_thuc_thanh_toan.required' => 'Phương thức thanh toán là bắt buộc.',
            'phuong_thuc_thanh_toan.in' => 'Phương thức thanh toán không hợp lệ.',
        ];
    }
}
