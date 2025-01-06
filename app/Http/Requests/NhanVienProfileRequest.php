<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
class NhanVienProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy id của nhân viên từ route hoặc thông tin đăng nhập
        $id = $this->route('id') ?? Auth::guard('sanctum')->user()->id;
        return [
            'email' => 'required|email|max:255|unique:nhan_viens,email,' . $id,
            'ho_va_ten' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\s\-]+$/u',
            ],
            'hinh_anh' => 'required|string|max:255',
            'ngay_sinh' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $ngay_sinh = Carbon::parse($value);
                    // Kiểm tra nếu ngày sinh là ngày trong tương lai
                    if ($ngay_sinh->isFuture()) {
                        $fail('Ngày sinh không hợp lệ vì là ngày trong tương lai');
                    }
                    // Kiểm tra nếu tuổi nhân viên dưới 18
                    $tuoi = $ngay_sinh->diffInYears(Carbon::now());
                    if ($tuoi < 18) {
                        $fail(' và nhân viên phải trên 18 tuổi.');
                    }
                }
            ],
            'gioi_tinh' => 'required|integer|in:0,1',
            'so_dien_thoai' => [
                'required',
                'regex:/^0[0-9]{9}$/',
                'unique:nhan_viens,so_dien_thoai,' . $id,  // Bỏ qua bản ghi hiện tại
            ],
            'dia_chi' => 'nullable|string|max:255',
            'kinh_nghiem' => [
                'required',
                'string',
                'regex:/^\d+\s*năm(\s+\d+\s*tháng)?$|^\d+\s*tháng(\s+\d+\s*năm)?$/i', // Cho phép "X năm Y tháng" hoặc "X tháng Y năm"
            ],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email phải là địa chỉ hợp lệ.',
            'email.unique' => 'Email này đã tồn tại.',
            'ho_va_ten.required' => 'Họ và tên không được để trống.',
            'ngay_sinh.required' => 'Ngày sinh không được để trống.',
            'so_dien_thoai.required' => 'Số điện thoại không được để trống.',
            'so_dien_thoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
            'so_dien_thoai.unique' => 'Số điện thoại này đã tồn tại.',
            'gioi_tinh.required' => 'Giới tính không được để trống.',
        ];
    }
}
