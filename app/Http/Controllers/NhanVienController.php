<?php

namespace App\Http\Controllers;

use App\Http\Requests\NhanVienDangNhapRequest;
use App\Http\Requests\NhanVienDoiMatKhauNoMailRequest;
use App\Http\Requests\NhanVienDoiMatKhauRequest;
use App\Http\Requests\NhanVienProfileRequest;
use App\Http\Requests\NhanVienRequest;
use App\Models\NhanVien;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\MasterMail;
use App\Models\ViDienTu;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
class NhanVienController extends Controller
{

    public function login(NhanVienDangNhapRequest $request)
    {
        $check = Auth::guard('nhanvien')->attempt([
            'email'    => $request->email,
            'password' => $request->password
        ]);

        if ($check) {
            // Lấy thông tin nhân viên đã đăng nhập
            $nhanVien = Auth::guard('nhanvien')->user();

            // Kiểm tra nếu nhân viên bị chặn (is_block = 1)
            if ($nhanVien->is_block == 1) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tài khoản của bạn đã bị chặn. Vui lòng liên hệ quản trị viên.'
                ]);
            }
            // Cập nhật trạng thái hoạt động (tinh_trang = 1, nghĩa là đang online)
            $nhanVien->tinh_trang = 1;
            $nhanVien->save();

            return response()->json([
                'status'  => true,
                'message' => 'Đăng nhập tài khoản nhân viên thành công',
                'token'   => $nhanVien->createToken('token_nhan_vien')->plainTextToken,
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
        $nhanVien = Auth::guard('sanctum')->user();
        // Kiểm tra nếu nhân viên tồn tại và là instance của model NhanVien
        if ($nhanVien && $nhanVien instanceof \App\Models\NhanVien) {
            $nhanVien->tinh_trang = 0;
            $nhanVien->save();
            DB::table('personal_access_tokens')
                ->where('id', $nhanVien->currentAccessToken()->id)
                ->delete();

            return response()->json([
                'status'     => true,
                'message'    => 'Đăng xuất tài khoản nhân viên thành công',
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
        $nhanVien  = Auth::guard('sanctum')->user();
        if ($nhanVien && $nhanVien instanceof \App\Models\NhanVien) {
            $ds_token = $nhanVien->tokens;
            foreach ($ds_token as $token) {
                $token->delete();
            }
            $nhanVien->tinh_trang = 0;
            $nhanVien->save();
            return response()->json([
                'status'     => true,
                'message'    => 'Đăng xuất tài khoản nhân viên trên tất cả trình duyệt thành công',
            ]);
        } else {
            return response()->json([
                'status'     => false,
                'message'    => 'Bạn cần đăng nhập hệ thống!',
            ]);
        }
    }


    public function getData()
    {
        $data = NhanVien::get();
        return response()->json([
            'status'     => true,
            'message'    => 'Lấy dữ liệu thành công',
            'data'       => $data,
        ]);
    }
    public function getDataChiTietNhanVien($id_nhan_vien)
    {
        $data = NhanVien::where('id',$id_nhan_vien)->first();
        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'không tìm thấy nhân viên.'
            ], 404);
        }
        return response()->json([
            'status'     => true,
            'message'    => 'Lấy dữ liệu nhân viên thành công',
            'data'       => $data,
        ]);
    }
    public function store(NhanVienRequest $request)
    {
        $ngay_sinh = Carbon::parse($request->ngay_sinh);
        $tuoi_nhan_vien = $ngay_sinh->age;
        $nhanVien = NhanVien::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'ho_va_ten' => $request->ho_va_ten,
            'hinh_anh' => $request->hinh_anh,
            'can_cuoc_cong_dan' => $request->can_cuoc_cong_dan,
            'ngay_sinh' => $request->ngay_sinh,
            'gioi_tinh' => $request->gioi_tinh,
            'so_dien_thoai' => $request->so_dien_thoai,
            'dia_chi' => $request->dia_chi,
            'tuoi_nhan_vien' => $tuoi_nhan_vien,
            'kinh_nghiem' => $request->kinh_nghiem,
            'tinh_trang' => $request->tinh_trang,
        ]);

        if (!$nhanVien) {
            return response()->json([
                'status' => false,
                'message' => 'Thêm nhân viên thất bại!'
            ], 500);
        }

        // Tạo ví điện tử cho nhân viên
        $viDienTu = ViDienTu::create([
            'nhan_vien_id' => $nhanVien->id,
        ]);

        if (!$viDienTu) {
            $nhanVien->delete();
            return response()->json([
                'status' => false,
                'message' => 'Tạo ví điện tử thất bại dẫn đến tạo nhân viên thất bại!',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Đã tạo mới nhân viên ' . $request->ho_va_ten . ' và ví điện tử thành công.',
        ]);
    }

    public function update(NhanVienRequest $request, $id)
    {
        $nhanVien = NhanVien::find($id);
        if (!$nhanVien) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy nhân viên!'
            ], 404);
        }
        $ngay_sinh = Carbon::parse($request->ngay_sinh);
        $tuoi_nhan_vien = $ngay_sinh->age;
        $data = [
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'ho_va_ten' => $request->ho_va_ten,
            'hinh_anh' => $request->hinh_anh,
            'can_cuoc_cong_dan' => $request->can_cuoc_cong_dan,
            'ngay_sinh' => $request->ngay_sinh,
            'gioi_tinh' => $request->gioi_tinh,
            'so_dien_thoai' => $request->so_dien_thoai,
            'dia_chi' => $request->dia_chi,
            'tuoi_nhan_vien' => $tuoi_nhan_vien,
            'kinh_nghiem' => $request->kinh_nghiem,
            'tinh_trang' => $request->tinh_trang,
        ];
        $nhanVien->update($data);
        return response()->json([
            'status' => true,
            'message' => 'Đã cập nhật thông tin nhân viên ' . $request->ho_va_ten . ' thành công.',
        ]);
    }
    public function destroy($id)
    {
        $nhanVien = NhanVien::find($id);
        if (!$nhanVien) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy nhân viên!'
            ], 404);
        }

        $nhanVien->delete();
        return response()->json([
            'status' => true,
            'message' => 'Đã xóa nhân viên ' . $nhanVien->ho_va_ten . ' thành công.',
        ]);
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
    public function updateProfile(NhanVienProfileRequest $request)
    {
        $tai_khoan_dang_dang_nhap   = Auth::guard('sanctum')->user();
        $ngay_sinh = Carbon::parse($request->ngay_sinh);
        $tuoi_nhan_vien = $ngay_sinh->age;
        $check = NhanVien::where('id', $tai_khoan_dang_dang_nhap->id)->update([
            'email'         => $request->email,
            'ho_va_ten'     => $request->ho_va_ten,
            'hinh_anh'      => $request->hinh_anh,
            'ngay_sinh'     => $request->ngay_sinh,
            'gioi_tinh'     => $request->gioi_tinh,
            'so_dien_thoai' => $request->so_dien_thoai,
            'dia_chi'       => $request->dia_chi,
            'tuoi_nhan_vien' => $tuoi_nhan_vien,
            'kinh_nghiem'   => $request->kinh_nghiem,
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
    public function checkNhanVien()
    {
        $tai_khoan_dang_dang_nhap   = Auth::guard('sanctum')->user();
        if ($tai_khoan_dang_dang_nhap && $tai_khoan_dang_dang_nhap instanceof \App\Models\NhanVien) {
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
    public function getDataNhanVien()
    {
        $data = NhanVien::orderBy('id', 'DESC')->get();
        return response()->json([
            'data' => $data
        ]);
    }
    public function getDataNhanVienNoiBat()
    {
        $data = NhanVien::where('is_noi_bat',1)->get();
        return response()->json([
            'data' => $data
        ]);
    }
    public function getDataNhanVienFlashSale()
    {
        $data = NhanVien::where('is_flash_sale',1)->take(4)->get();
        return response()->json([
            'data' => $data
        ]);
    }
    public function changeBlock(Request $request)
    {
        $nhanVien = NhanVien::where('id', $request->id)->first();
        if ($nhanVien) {
            $nhanVien->is_block = !$nhanVien->is_block;
            $nhanVien->save();
            $message = $nhanVien->is_block ? "Đã chặn tài khoản thành công!" : "Đã mở chặn tài khoản thành công!";
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

    public function changeNoiBat(Request $request)
    {
        $nhanVien = NhanVien::find($request->id);
        if ($nhanVien) {
            $is_noi_bat = $nhanVien->is_noi_bat == 1 ? 0 : 1;
            $nhanVien->update([
                'is_noi_bat' => $is_noi_bat
            ]);
            return response()->json([
                'status' => true,
                'message' => "Đã đổi tình trạng nhân viên " . $request->ho_va_ten . " thành công.",
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Nhân viên không tồn tại.'
            ]);
        }
    }

    public function changeFlashSale(Request $request){
        $is_flash_sale = $request->is_flash_sale == 1 ? 0 : 1;
        NhanVien::find($request->id)->update([
            'is_flash_sale'    =>  $is_flash_sale
        ]);

        return response()->json([
            'status' => true,
            'message' => "Đã đổi tình trạng nhân viên ". $request->ho_va_ten . " thành công.",
        ]);
    }
    public function quenMK(Request $request)
    {
        $nhanVien = NhanVien::where('email', $request->email)->first();
        if($nhanVien){
            $hash_reset         = Str::uuid();
            $x['ho_va_ten']     = $nhanVien->ho_va_ten;
            $x['link']          = 'http://localhost:5173/nhan-vien/laymatkhau/' . $hash_reset;
            Mail::to($request->email)->send(new MasterMail('Đổi Mật Khẩu Của Bạn', 'NhanVienQuenMatKhau', $x));
            $nhanVien->hash_reset = $hash_reset;
            $nhanVien->save();
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

    public function doiMK(NhanVienDoiMatKhauRequest $request)
    {
        $nhanVien = NhanVien::where('hash_reset', $request->id)->first();
        $nhanVien->password = bcrypt($request->password);
        $nhanVien->hash_reset = NULL;
        $nhanVien->save();

        return response()->json([
            'status'    =>  true,
            'message'   =>  'Đã đổi mật khẩu thành công'
        ]);
    }
    public function quenMKNoMail(NhanVienDoiMatKhauNoMailRequest $request)
    {
        $nhanVien = Auth::guard('sanctum')->user();
        NhanVien::Where('id', $nhanVien->id)->update(['password'=>bcrypt($request->password)]);
        return response()->json([
            'status'    =>  true,
            'message'   =>  'Đã đổi mật khẩu thành công'
        ]);
    }
    public function changeAnhProfile(Request $request)
    {
        // Lấy thông tin nhân viên đã đăng nhập
        $nhanVien = Auth::guard('sanctum')->user();

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
        $file_name = Str::slug($nhanVien->ho_va_ten) . "-" . Str::slug($nhanVien->so_dien_thoai) . "." . $file_extension;

        // Thư mục lưu ảnh
        $destinationPath = public_path('nhanVienAVT');

        // Đường dẫn lưu ảnh mới
        $cho_luu = $destinationPath . DIRECTORY_SEPARATOR . $file_name;

        // Kiểm tra nếu nhân viên đã có ảnh đại diện trước đó
        if (!empty($nhanVien->hinh_anh)) {
            // Tách đường dẫn ảnh cũ để xóa file
            $oldImagePath = str_replace(url('/') . '/', '', $nhanVien->hinh_anh);

            // Xóa ảnh cũ nếu tồn tại
            if (file_exists(public_path($oldImagePath))) {
                unlink(public_path($oldImagePath));
            }
        }

        // Di chuyển file mới vào thư mục
        $file->move($destinationPath, $file_name);

        // Tạo đường dẫn ảnh mới
        $hinh_anh = url('nhanVienAVT/' . $file_name);

        // Cập nhật thông tin trong database
        NhanVien::find($nhanVien->id)->update([
            'hinh_anh' => $hinh_anh,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đã đổi ảnh đại diện profile thành công!',
            'new_image' => $hinh_anh
        ]);
    }
}
