<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class AdminProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Cho phép thực hiện request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Lấy id từ route hoặc từ người dùng đang đăng nhập
        $id = $this->route('id') ?? Auth::guard('sanctum')->user()->id;
        return [
            'email' => 'required|email|max:255|unique:admins,email,' . $id,
            'ho_va_ten' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\s\-]+$/u',
            ],
            'ngay_sinh' => 'required|date|before:today',
            'gioi_tinh' => 'required|in:0,1',
            'so_dien_thoai' => [
                'required',
                'regex:/^0[0-9]{9}$/',
                'unique:admins,so_dien_thoai,' . $id,
            ],
            'dia_chi' => 'required|string|max:255',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages()
    {
        return [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã tồn tại.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',

            'ho_va_ten.required' => 'Họ và tên không được để trống.',
            'ho_va_ten.max' => 'Họ và tên không được vượt quá 255 ký tự.',

            'ngay_sinh.required' => 'Ngày sinh không được để trống.',
            'ngay_sinh.date' => 'Ngày sinh không hợp lệ.',
            'ngay_sinh.before' => 'Ngày sinh phải trước ngày hiện tại.',

            'gioi_tinh.required' => 'Giới tính không được để trống.',
            'gioi_tinh.in' => 'Giới tính phải là 0 (Nam) hoặc 1 (Nữ).',

            'so_dien_thoai.required' => 'Số điện thoại không được để trống.',
            'so_dien_thoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.',
            'so_dien_thoai.unique' => 'Số điện thoại này đã tồn tại.',

            'dia_chi.required' => 'Địa chỉ không được để trống.',
            'dia_chi.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
        ];
    }
}
