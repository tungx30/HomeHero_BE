<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DanhGiaNhanVienRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'so_sao' => 'required|integer|min:1|max:5',
            'nhan_xet' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'so_sao.required' => 'Vui lòng chọn số sao để đánh giá.',
            'so_sao.integer' => 'Số sao phải là một số nguyên.',
            'so_sao.min' => 'Số sao phải tối thiểu là 1.',
            'so_sao.max' => 'Số sao tối đa là 5.',
            'nhan_xet.string' => 'Nhận xét phải là chuỗi ký tự.',
            'nhan_xet.max' => 'Nhận xét không được vượt quá 500 ký tự.',
        ];
    }
}
