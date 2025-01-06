<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LuaChonDichVuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Lấy id từ route
        $id = $this->route('id'); // 'id' là tham số trong URL

        return [
            'ten_lua_chon' => 'required|string|unique:lua_chon_dich_vus,ten_lua_chon,' . ($id ? $id : 'NULL') . '|max:255',
            'slug_dich_vu' => 'string|unique:lua_chon_dich_vus,slug_dich_vu,' . ($id ? $id : 'NULL') . '|max:255',
            'icon_dich_vu' => 'required|string|max:255',
            'id_muc' => 'required|exists:danh_muc_dich_vus,id',
            'is_active' => 'nullable|integer|in:0,1',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages()
    {
        return [
            'ten_lua_chon.required' => 'Tên lựa chọn dịch vụ không được bỏ trống.',
            'ten_lua_chon.unique' => 'Tên lựa chọn dịch vụ này đã tồn tại.',
            'slug_dich_vu.required' => 'Slug của dịch vụ không được bỏ trống.',
            'slug_dich_vu.unique' => 'Slug của dịch vụ này đã tồn tại.',
            'icon_dich_vu.required' => 'Biểu tượng của dịch vụ không được bỏ trống.',
            'id_muc.required' => 'Mã danh mục dịch vụ không được bỏ trống.',
            'id_muc.exists' => 'Mã danh mục dịch vụ không tồn tại trong hệ thống.',
            'is_active.in' => 'Giá trị is_active chỉ được phép là 0 hoặc 1.',
        ];
    }
}
