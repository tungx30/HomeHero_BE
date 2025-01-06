<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminDangNhapRequest;
use App\Http\Requests\AdminDoiMatKhauNoMailRequest;
use App\Http\Requests\AdminDoiMatKhauRequest;
use App\Http\Requests\AdminProfileRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\MasterMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class AdminController extends Controller
{

    public function login(AdminDangNhapRequest $request)
    {
        $check = Auth::guard('admin')->attempt([
            'email'         => $request->email,
            'password'      => $request->password
        ]);
        if($check){
            $admin = Auth::guard('admin')->user();
            return response()->json([
                'status'    => true,
                'message'   => 'Đăng nhập thành công',
                'token'     => $admin->createToken('token_admin')->plainTextToken,
            ]);
        }
        else{
            return response()->json([
                'status'    => false,
                'message'   => 'Kiểm tra lại email hoặc mật khẩu',
            ]);
        }
    }

    public function logout(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if($admin && $admin instanceof \App\Models\Admin){
            DB::table('personal_access_tokens')
                ->where('id', $admin->currentAccessToken()->id)
                ->delete();
            return response()->json([
                'status'     => true,
                'message'    =>  'Đăng xuất thành công',
            ]);
        }
        else{
            return response()->json([
                'status'     => false,
                'message'    =>  'Bạn cần đăng nhập hệ thống!',
           ]);
        }
    }

    public function logoutAll()
    {
        $admin  = Auth::guard('sanctum')->user();
        if($admin && $admin instanceof \App\Models\Admin) {
           $ds_token = $admin->tokens;
           foreach ($ds_token as $token) {
               $token->delete();
           }
            return response()->json([
                 'status'     => true,
                 'message'    =>  'Đăng xuất tất cả trình duyệt thành công',
            ]);
        } else {
         return response()->json([
             'status'     => false,
             'message'    =>  'Bạn cần đăng nhập hệ thống!',
        ]);
        }
    }
    public function getDataProfile()
    {
        // ở đây là chúng ta đang lấy thông tin của người đang đăng nhập ra
        $data = Auth::guard('sanctum')->user();
        return response()->json([
            'status'     => true,
            'message'    => 'Lấy dữ liệu thành công',
            'data'       => $data,
        ]);
    }
    public function updateProfile(AdminProfileRequest $request)
    {
        $tai_khoan_dang_dang_nhap   = Auth::guard('sanctum')->user();
        $check = Admin::where('id', $tai_khoan_dang_dang_nhap->id)->update([
            'email'         => $request->email,
            'ho_va_ten'     => $request->ho_va_ten,
            'ngay_sinh'     => $request->ngay_sinh,
            'gioi_tinh'     => $request->gioi_tinh,
            'so_dien_thoai' => $request->so_dien_thoai,
            'dia_chi'       => $request->dia_chi,
        ]);

        if($check) {
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
    public function checkAdmin()
    {
        $tai_khoan_dang_dang_nhap   = Auth::guard('sanctum')->user();
        if($tai_khoan_dang_dang_nhap && $tai_khoan_dang_dang_nhap instanceof \App\Models\Admin) {
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
        $admin = Admin::where('email', $request->email)->first();
        if($admin){
            $hash_reset         = Str::uuid();
            $x['ho_va_ten']     = $admin->ho_va_ten;
            $x['link']          = 'http://localhost:5173/admin/laymatkhau/' . $hash_reset;
            Mail::to($request->email)->send(new MasterMail('Đổi Mật Khẩu Của admin', 'AdminQuenMK', $x));
            $admin->hash_reset = $hash_reset;
            $admin->save();
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
    public function doiMK(AdminDoiMatKhauRequest $request)
    {
        $admin = Admin::where('hash_reset', $request->hash_reset)->first();
        $admin->password = bcrypt($request->password);
        $admin->hash_reset = NULL;
        $admin->save();

        return response()->json([
            'status'    =>  true,
            'message'   =>  'Đã đổi mật khẩu thành công'
        ]);
    }
    public function quenMKNoMail(AdminDoiMatKhauNoMailRequest $request)
    {
        $admin = Auth::guard('sanctum')->user();
        Admin::Where('id', $admin->id)->update(['password'=>bcrypt($request->password)]);
        return response()->json([
            'status'    =>  true,
            'message'   =>  'Đã đổi mật khẩu thành công'
        ]);
    }
    public function changeAnhProfile(Request $request)
    {
        // Lấy thông tin admin đã đăng nhập
        $admin = Auth::guard('sanctum')->user();

        // Kiểm tra nếu không có file ảnh trong request
        if (!$request->hasFile('hinh_anh')) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng chọn một ảnh để cập nhật!'
            ], 400);
        }

        $file = $request->file('hinh_anh');

        // Lấy phần mở rộng của file và tạo tên file mới
        $file_extension = $file->getClientOriginalExtension();
        $file_name = Str::slug($admin->ho_va_ten) . "-" . Str::slug($admin->so_dien_thoai) . "." . $file_extension;

        // Thư mục lưu ảnh
        $destinationPath = public_path('AdminAVT');

        // Đường dẫn lưu ảnh mới
        $cho_luu = $destinationPath . DIRECTORY_SEPARATOR . $file_name;

        // Kiểm tra nếu admin đã có ảnh đại diện trước đó
        if (!empty($admin->hinh_anh)) {
            // Tách đường dẫn ảnh cũ để xóa file
            $oldImagePath = str_replace(url('/') . '/', '', $admin->hinh_anh);

            // Xóa ảnh cũ nếu tồn tại
            if (file_exists(public_path($oldImagePath))) {
                unlink(public_path($oldImagePath));
            }
        }

        // Di chuyển file mới vào thư mục
        $file->move($destinationPath, $file_name);

        // Tạo đường dẫn ảnh mới
        $hinh_anh = url('AdminAVT/' . $file_name);

        // Cập nhật thông tin trong database
        Admin::find($admin->id)->update([
            'hinh_anh' => $hinh_anh,
        ]);

        // Ghi log để kiểm tra (tùy chọn)
        Log::info("Ảnh đại diện mới của admin: " . $hinh_anh);

        return response()->json([
            'status' => true,
            'message' => 'Đã đổi ảnh đại diện profile thành công!',
            'new_image' => $hinh_anh
        ]);
    }
}
