<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class NhanVienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'email' => 'required|email|unique:nhan_viens,email,' . ($id ?? 'NULL'),
            'password' => 'required|string|min:6',
            'ho_va_ten' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\s\-\.]+$/u',
            ],
            'hinh_anh' => 'required|string|max:255',
            'can_cuoc_cong_dan' => [
                'required',
                'regex:/^0\d{11}$/',
                'unique:nhan_viens,can_cuoc_cong_dan,' . ($id ?? 'NULL')
            ],
            'ngay_sinh' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $ngay_sinh = Carbon::parse($value);
                    if ($ngay_sinh->isFuture()) {
                        $fail('Ngày sinh không hợp lệ vì là ngày trong tương lai');
                    }
                    $tuoi = $ngay_sinh->diffInYears(Carbon::now());
                    if ($tuoi < 18) {
                        $fail(' và nhân viên phải trên 18 tuổi.');
                    }
                }
            ],
            'gioi_tinh' => 'required|integer|in:0,1,2',
            'so_dien_thoai' => [
                'required',
                'regex:/^0[0-9]{9}$/',
                'unique:nhan_viens,so_dien_thoai,' . ($id ?? 'NULL'),
            ],
            'dia_chi' => 'nullable|string|max:255',
            'tinh_trang' => 'required|integer|in:0,1',
            'kinh_nghiem' => [
                'required',
                'string',
                'regex:/^\d+\s*năm(\s+\d+\s*tháng)?$|^\d+\s*tháng(\s+\d+\s*năm)?$/i',
            ],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email phải là địa chỉ hợp lệ.',
            'email.unique' => 'Email này đã tồn tại.',
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'ho_va_ten.required' => 'Họ và tên không được để trống.',
            'ho_va_ten.regex' => 'Họ và tên chỉ được chứa chữ cái, dấu gạch ngang, dấu chấm và khoảng trắng.',
            'can_cuoc_cong_dan.required' => 'Căn cước công dân không được để trống.',
            'can_cuoc_cong_dan.regex' => 'Căn cước công dân phải bắt đầu bằng số 0 và có đúng 12 chữ số.',
            'can_cuoc_cong_dan.unique' => 'Căn cước công dân này đã tồn tại.',
            'ngay_sinh.required' => 'Ngày sinh không được để trống.',
            'so_dien_thoai.required' => 'Số điện thoại không được để trống.',
            'so_dien_thoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
            'so_dien_thoai.unique' => 'Số điện thoại này đã tồn tại.',
            'dia_chi.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'tinh_trang.required' => 'Tình trạng hoạt động của nhân viên không được để trống.',
            'gioi_tinh.required' => 'Giới tính không được để trống.',
            'kinh_nghiem.required' => 'Kinh nghiệm là bắt buộc.',
            'kinh_nghiem.regex' => 'Kinh nghiệm phải nhập theo định dạng số kèm theo "tháng" hoặc "năm".',
        ];
    }
}
