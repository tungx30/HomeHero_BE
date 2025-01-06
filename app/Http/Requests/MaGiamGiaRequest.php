<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaGiamGiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        $loaiGiamGia = $this->input('loai_giam_gia');

        return [
            'code' => 'required|string|max:255|unique:ma_giam_gias,code,' . $id,
            'tinh_trang' => 'required|integer|in:0,1',
            'ngay_bat_dau' => 'required|date',
            'ngay_ket_thuc' => 'required|date|after_or_equal:ngay_bat_dau',
            'loai_giam_gia' => 'required|integer|in:1,2',
            'so_giam_gia' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($loaiGiamGia) {
                    $soTienToiDa = $this->input('so_tien_toi_da');
                    // Kiểm tra theo loại giảm giá
                    if ($loaiGiamGia == 1 && ($value > 100 || ($value / 100) * $this->input('dk_toi_thieu_don_hang') > $soTienToiDa)) {
                        $fail('Số phần trăm giảm giá không hợp lệ hoặc vượt quá số tiền tối đa.');
                    }
                    if ($loaiGiamGia == 2 && $value > $soTienToiDa) {
                        $fail('Số tiền giảm giá không được vượt quá số tiền giảm tối đa.');
                    }
                }
            ],
            'so_tien_toi_da' => 'required|integer|min:0',
            'dk_toi_thieu_don_hang' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã giảm giá không được để trống.',
            'code.unique' => 'Mã giảm giá này đã tồn tại.',
            'tinh_trang.required' => 'Tình trạng áp dụng là bắt buộc.',
            'ngay_bat_dau.required' => 'Ngày bắt đầu là bắt buộc.',
            'ngay_bat_dau.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi.',
            'ngay_ket_thuc.required' => 'Ngày kết thúc là bắt buộc.',
            'ngay_ket_thuc.after_or_equal' => 'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu.',
            'loai_giam_gia.required' => 'Loại giảm giá là bắt buộc.',
            'so_giam_gia.required' => 'Số giảm giá sẽ giảm là bắt buộc.',
            'so_tien_toi_da.required' => 'Số tiền giảm tối đa là bắt buộc.',
            'dk_toi_thieu_don_hang.required' => 'Điều kiện tối thiểu của đơn hàng là bắt buộc.',
        ];
    }
}
