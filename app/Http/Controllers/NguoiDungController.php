<?php

namespace App\Http\Controllers;

use App\Http\Requests\NguoiDungDangNhapRequest;
use App\Http\Requests\NguoiDungDoiMatKhauNoMailRequest;
use App\Http\Requests\NguoiDungDoiMatKhauRequest;
use App\Http\Requests\NguoiDungProfileRequest;
use App\Http\Requests\NguoiDungRequest;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\MasterMail;
use App\Models\ViDienTuNguoiDung;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NguoiDungController extends Controller
{
    public function login(NguoiDungDangNhapRequest $request)
    {
        $check = Auth::guard('nguoidung')->attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);


        if ($check) {
            $nguoidung = Auth::guard('nguoidung')->user();
            //check đã bị chặn
            if ($nguoidung->is_block == 1) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tài khoản của bạn đã bị chặn. Vui lòng liên hệ với hotline để được hỗ trợ.'
                ]);
            }
            //check chưa kích hoạt
            if ($nguoidung->is_active == 0) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tài khoản của bạn chưa được kích hoạt. Vui lòng kiểm tra email để kích hoạt tài khoản hoặc liên hệ với hỗ trợ khách hàng nếu không nhận được gmail.'
                ]);
            }
            // Cập nhật trạng thái hoạt động (tinh_trang = 1, nghĩa là đang online)
            $nguoidung->tinh_trang = 1;
            if (!$nguoidung->save()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật trạng thái tài khoản. Vui lòng thử lại.'
                ]);
            }
            return response()->json([
                'status'  => true,
                'message' => 'Đăng nhập tài khoản thành công',
                'token'   => $nguoidung->createToken('token_nguoi_dung')->plainTextToken,
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Kiểm tra lại email hoặc mật khẩu',
            ]);
        }
    }
    public function logout()
    {
        //lấy thông tin đăng nhập
        $nguoidung = Auth::guard('sanctum')->user();
        // Kiểm tra nếu người dùng tồn tại và là instance của model nguoidun$nguoidung
        if ($nguoidung && $nguoidung instanceof \App\Models\NguoiDung) {
            $nguoidung->tinh_trang = 0;
            $nguoidung->save();
            DB::table('personal_access_tokens')
                ->where('id', $nguoidung->currentAccessToken()->id)
                ->delete();
            return response()->json([
                'status'     => true,
                'message'    => 'Đăng xuất tài khoản thành công',
            ]);
        } else {
            return response()->json([
                'status'     => false,
                'message'    => 'Bạn cần đăng nhập hệ thống!',
            ]);
        }
    }
    public function logoutAll()
    {
        $nguoidung  = Auth::guard('sanctum')->user();
        if ($nguoidung && $nguoidung instanceof \App\Models\NguoiDung) {
            $ds_token = $nguoidung->tokens;
            foreach ($ds_token as $token) {
                $token->delete();
            }
            $nguoidung->tinh_trang = 0;
            $nguoidung->save();
            return response()->json([
                'status'     => true,
                'message'    => 'Đăng xuất tài khoản trên tất cả trình duyệt thành công',
            ]);
        } else {
            return response()->json([
                'status'     => false,
                'message'    => 'Bạn cần đăng nhập hệ thống!',
            ]);
        }
    }
    public function register(NguoiDungRequest $request)
    {
        $hash_active = Str::uuid();
        $nguoidung = NguoiDung::create([
            'email'         => $request->email,
            'password'      => bcrypt($request->password),
            'ho_va_ten'     => $request->ho_va_ten,
            'ngay_sinh'     => $request->ngay_sinh,
            'gioi_tinh'     => $request->gioi_tinh,
            'so_dien_thoai' => $request->so_dien_thoai,
            'hash_active'   => $hash_active,
        ]);

        if (!$nguoidung) {
            return response()->json([
                'status' => false,
                'message' => 'Đăng ký thất bại!'
            ], 500);
        }
        ViDienTuNguoiDung::create([
            'nguoi_dung_id' => $nguoidung->id,
            'so_du'         => 0,
            'tinh_trang'    => 0,
        ]);

        $data['ho_va_ten'] = $request->ho_va_ten;
        $data['link'] = 'http://localhost:5173/nguoi-dung/kich-hoat/' . $hash_active;

        Mail::to($request->email)->send(new MasterMail('Kích Hoạt Tài Khoản', 'NguoiDungDangKy', $data));

        return response()->json([
            'status' => true,
            'message' => "Đăng Kí Tài Khoản Thành Công!, Hãy kiểm tra mail của bạn "
        ]);
    }
    public function kichHoat(Request $request)
    {
        $nguoiDung = NguoiDung::where('hash_active', $request->id_nguoi_dung)->first();
        if ($nguoiDung && $nguoiDung->is_active == 0) {
            $nguoiDung->is_active = 1;
            $nguoiDung->save();
            return response()->json([
                'status'    =>  true,
                'message'   =>  'Đã kích hoạt tài khoản thành công. Vui Lòng đăng nhập lại'
            ]);
        } else {
            return response()->json([
                'status'    =>  false,
                'message'   =>  'Liên kết không tồn tại'
            ]);
        }
    }
    public function getDataProfile()
    {
        $data = Auth::guard('sanctum')->user();
        return response()->json([
            'status'     => true,
            'message'    => 'Lấy dữ liệu thành công',
            'data'       => $data,
        ]);
    }
    public function updateProfile(NguoiDungProfileRequest $request)
    {
        $tai_khoan_dang_dang_nhap   =  Auth::guard('sanctum')->user();
        $check = NguoiDung::where('id', $tai_khoan_dang_dang_nhap->id)->update([
            'email'         => $request->email,
            'ho_va_ten'     => $request->ho_va_ten,
            'ngay_sinh'     => $request->ngay_sinh,
            'gioi_tinh'     => $request->gioi_tinh,
            'so_dien_thoai' => $request->so_dien_thoai,
        ]);

        if ($check) {
            return response()->json([
                'status'    =>  true,
                'message'   =>  'Cập nhật profile thành công'
            ]);
        } else {
            return response()->json([
                'status'    =>  false,
                'message'   =>  'Cập nhật thất bại'
            ]);
        }
    }
    public function getData()
    {
        $data = NguoiDung::get();
        return response()->json([
            'status'     => true,
            'message'    => 'Lấy dữ liệu thành công',
            'data'       => $data,
        ]);
    }
    public function store(NguoiDungRequest $request)
    {
        $nguoidung = NguoiDung::create([
            'email'         => $request->email,
            'password'      => bcrypt($request->password),
            'ho_va_ten'     => $request->ho_va_ten,
            'ngay_sinh'     => $request->ngay_sinh,
            'gioi_tinh'     => $request->gioi_tinh,
            'so_dien_thoai' => $request->so_dien_thoai,
        ]);

        if (!$nguoidung) {
            return response()->json([
                'status' => false,
                'message' => 'Thêm người dùng thất bại!'
            ], 500);
        }
        ViDienTuNguoiDung::create([
            'nguoi_dung_id' => $nguoidung->id,
            'so_du'         => 0,
            'tinh_trang'    => 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đã tạo mới người dùng ' . $request->ho_va_ten . ' thành công.',
        ]);
    }
    public function update(NguoiDungRequest $request, $id)
    {
        $nguoidung = NguoiDung::find($id);
        if (!$nguoidung) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy nhân viên!'
            ], 404);
        }
        // Lưu dữ liệu chuẩn bị để cập nhật
        $data = [
            'email'         => $request->email,
            'password'      => bcrypt($request->password),
            'ho_va_ten'     => $request->ho_va_ten,
            'ngay_sinh'     => $request->ngay_sinh,
            'gioi_tinh'     => $request->gioi_tinh,
            'so_dien_thoai' => $request->so_dien_thoai,
        ];
        // Cập nhật thông tin nhân viên
        $nguoidung->update($data);
        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật thông tin người dùng ' . $request->ho_va_ten . ' thành công.',
        ]);
    }
    public function destroy($id)
    {
        $nguoidung = NguoiDung::find($id);
        if (!$nguoidung) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy người dùng!'
            ], 404);  // Trả về lỗi 404 nếu không tìm thấy nhân viên
        }
        $nguoidung->delete();
        return response()->json([
            'status' => true,
            'message' => 'Đã xóa người dùng ' . $nguoidung->ho_va_ten . ' thành công.',
        ]);
    }

    public function kichHoatTaiKhoan(Request $request)
    {
        $nguoidung = NguoiDung::where('id', $request->id)->first();
        if ($nguoidung) {
            if ($nguoidung->is_active == 0) {
                $nguoidung->is_active = 1;
                $nguoidung->save();

                return response()->json([
                    'status' => true,
                    'message' => "Đã kích hoạt tài khoản thành công!"
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Có lỗi xảy ra!"
            ]);
        }
    }
    public function changeBlock(Request $request)
    {
        $nguoidung = NguoiDung::where('id', $request->id)->first();
        if ($nguoidung) {
            $nguoidung->is_block = !$nguoidung->is_block;
            $nguoidung->save();
            $message = $nguoidung->is_block ? "Đã chặn tài khoản thành công!" : "Đã mở chặn tài khoản thành công!";
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Có lỗi xảy ra!"
            ]);
        }
    }
    public function checkNguoiDung()
    {
        $tai_khoan_dang_dang_nhap   = Auth::guard('sanctum')->user();
        if ($tai_khoan_dang_dang_nhap && $tai_khoan_dang_dang_nhap instanceof \App\Models\NguoiDung) {
            return response()->json([
                'status'    =>  true
            ]);
        } else {
            return response()->json([
                'status'    =>  false,
                'message'   =>  'Bạn cần đăng nhập hệ thống trước'
            ]);
        }
    }
    public function quenMK(Request $request)
    {
        $nguoi_dung = NguoiDung::where('email', $request->email)->first();
        if ($nguoi_dung) {
            $hash_reset         = Str::uuid();
            $data['ho_va_ten']     = $nguoi_dung->ho_va_ten;
            $data['link']          = 'http://localhost:5173/nguoi-dung/laymatkhau/' . $hash_reset;
            Mail::to($request->email)->send(new MasterMail('Đổi Mật Khẩu Của Bạn', 'NguoiDungQuenMatKhau', $data));
            $nguoi_dung->hash_reset = $hash_reset;
            $nguoi_dung->save();
            return response()->json([
                'status'    =>  true,
                'message'   =>  'Vui Lòng kiểm tra lại email'
            ]);
        } else {
            return response()->json([
                'status'    =>  false,
                'message'   =>  'Email không có trong hệ thống'
            ]);
        }
    }

    public function doiMK(NguoiDungDoiMatKhauRequest $request)
    {
        $nguoiDung = NguoiDung::where('hash_reset', $request->id)->first();
        $nguoiDung->password = bcrypt($request->password);
        $nguoiDung->hash_reset = NULL;
        $nguoiDung->save();

        return response()->json([
            'status'    =>  true,
            'message'   =>  'Đã đổi mật khẩu thành công'
        ]);
    }
    public function quenMKNoMail(NguoiDungDoiMatKhauNoMailRequest $request)
    {
        $nguoiDung = Auth::guard('sanctum')->user();
        NguoiDung::Where('id', $nguoiDung->id)->update(['password' => bcrypt($request->password)]);
        return response()->json([
            'status'    =>  true,
            'message'   =>  'Đã đổi mật khẩu thành công'
        ]);
    }
    public function changeAnhProfile(Request $request)
    {
        // Lấy thông tin người dùng đã đăng nhập
        $nguoiDung = Auth::guard('sanctum')->user();
        $data = $request->all();

        // Kiểm tra xem file hình ảnh có được gửi hay không
        if (!$request->hasFile('hinh_anh')) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng chọn một ảnh để cập nhật!'
            ], 400);
        }

        $file = $request->file('hinh_anh');

        // Lấy phần mở rộng và tạo tên file mới
        $file_extension = $file->getClientOriginalExtension();
        $file_name = Str::slug($nguoiDung->ho_va_ten) . "-" . Str::slug($nguoiDung->so_dien_thoai) . "." . $file_extension;

        // Thư mục lưu ảnh
        $destinationPath = public_path('NguoiDungAVT');

        // Đường dẫn lưu ảnh mới
        $cho_luu = $destinationPath . DIRECTORY_SEPARATOR . $file_name;

        // Kiểm tra xem người dùng đã có ảnh đại diện hay chưa
        if (!empty($nguoiDung->hinh_anh)) {
            // Tách đường dẫn ảnh cũ để xóa file
            $oldImagePath = str_replace(url('/') . '/', '', $nguoiDung->hinh_anh);

            // Xóa ảnh cũ nếu tồn tại
            if (file_exists(public_path($oldImagePath))) {
                unlink(public_path($oldImagePath));
            }
        }

        // Di chuyển file mới vào thư mục
        $file->move($destinationPath, $file_name);

        // Lưu đường dẫn ảnh mới
        $hinh_anh = url('NguoiDungAVT/' . $file_name);

        // Cập nhật thông tin trong database
        $nguoiDung->update([
            'hinh_anh' => $hinh_anh,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đã đổi ảnh đại diện profile thành công!',
            'new_image' => $hinh_anh
        ]);
    }
}
