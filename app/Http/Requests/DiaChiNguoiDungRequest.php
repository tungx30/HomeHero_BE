<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DiaChiNguoiDungRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->route('id') ?? Auth::guard('sanctum')->user()->id;
        return [
            'dia_chi' => 'required|string|max:255',
            'ten_nguoi_nhan' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\s\-\.]+$/u',  // Chỉ cho phép chữ cái, khoảng trắng, dấu gạch ngang, và dấu chấm
            ],
            'so_dien_thoai' => [
                'required',
                'regex:/^0[0-9]{9}$/', // Kiểm tra số điện thoại hợp lệ (10 chữ số, bắt đầu bằng 0)
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'dia_chi.required' => 'Địa chỉ là bắt buộc.',
            'dia_chi.string' => 'Địa chỉ phải là chuỗi ký tự hợp lệ.',
            'dia_chi.max' => 'Địa chỉ không được vượt quá 255 ký tự.',

            'ten_nguoi_nhan.required' => 'Tên người nhận là bắt buộc.',
            'ten_nguoi_nhan.string' => 'Tên người nhận phải là chuỗi ký tự hợp lệ.',
            'ten_nguoi_nhan.max' => 'Tên người nhận không được vượt quá 255 ký tự.',
            'ten_nguoi_nhan.regex' => 'Tên người nhận chỉ được chứa chữ cái, dấu gạch ngang, dấu chấm và khoảng trắng.',

            'so_dien_thoai.required' => 'Số điện thoại là bắt buộc.',
            'so_dien_thoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
        ];
    }
}
