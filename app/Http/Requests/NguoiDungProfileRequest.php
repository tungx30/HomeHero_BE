<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class NguoiDungProfileRequest extends FormRequest
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
        $id = $this->route('id') ?? Auth::guard('sanctum')->user()->id;
        return [
            'email' => 'required|email|unique:nguoi_dungs,email,' . $id,
            'ho_va_ten' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\s\-\.]+$/u',  // Chỉ cho phép chữ cái, khoảng trắng, dấu gạch ngang, và dấu chấm
            ],
           'hinh_anh' => 'nullable|string|max:255',
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
                        $fail(' và bạn phải trên 18 tuổi.');
                    }
                }
            ],
            'gioi_tinh' => 'required|integer|in:0,1,2',
            'so_dien_thoai' => [
                'required',
                'regex:/^0[0-9]{9}$/',
                'unique:nguoi_dungs,so_dien_thoai,' . $id,
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
            'ho_va_ten.regex' => 'Họ và tên chỉ được chứa chữ cái, dấu gạch ngang, dấu chấm và khoảng trắng.',
            'ngay_sinh.required' => 'Ngày sinh không được để trống.',
            'gioi_tinh.required' => 'Giới tính không được để trống.',
            'so_dien_thoai.required' => 'Số điện thoại không được để trống.',
            'so_dien_thoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
            'so_dien_thoai.unique' => 'Số điện thoại này đã tồn tại.',
        ];
    }

}
