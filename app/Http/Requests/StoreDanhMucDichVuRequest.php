<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDanhMucDichVuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;  // Cho phép tất cả người dùng thực hiện
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Lấy id từ route
        $id = $this->route('id'); // Lấy 'id' từ URL route nếu có

        return [
            'ten_muc' => 'required|string|unique:danh_muc_dich_vus,ten_muc,' . ($id ? $id : 'NULL') . '|max:255',
            'so_tien' => 'required|integer|min:0',
            'is_active' => 'nullable|integer|in:0,1',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages()
    {
        return [
            'ten_muc.required' => 'Tên mục không được bỏ trống.',
            'ten_muc.unique' => 'Tên mục này đã tồn tại.',
            'ten_muc.max' => 'Tên mục không được vượt quá 255 ký tự.',
            'slug_ten_muc.required' => 'Slug của tên mục không được bỏ trống.',
            'slug_ten_muc.unique' => 'Slug của tên mục này đã tồn tại.',
            'slug_ten_muc.max' => 'Slug không được vượt quá 255 ký tự.',
            'so_tien.required' => 'Số tiền không được bỏ trống.',
            'so_tien.integer' => 'Số tiền phải là số nguyên.',
            'so_tien.min' => 'Số tiền phải lớn hơn hoặc bằng 0.',
            'is_active.in' => 'Giá trị is_active chỉ được phép là 0 hoặc 1.',
        ];
    }
}

