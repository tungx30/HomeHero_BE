<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\DanhMucDichVu;

class ChatbotController extends Controller
{
        public function getAllMaGiamGia()
    {
        try {
            // Lấy tất cả các mã giảm giá đang hoạt động
            $maGiamGias = \App\Models\MaGiamGia::select(
                'code',
                'tinh_trang',
                'ngay_bat_dau',
                'ngay_ket_thuc',
                'loai_giam_gia',
                'so_giam_gia',
                'so_tien_toi_da',
                'dk_toi_thieu_don_hang'
            )->where('tinh_trang', 1) // Chỉ lấy các mã giảm giá hoạt động
            ->get();

            // Biến đổi dữ liệu thành mảng JSON
            $result = $maGiamGias->map(function ($maGiamGia) {
                $loaiGiamGia = $maGiamGia->loai_giam_gia == 0 ? 'Giảm giá cố định' : 'Giảm giá theo phần trăm';
                $giaTri = $maGiamGia->loai_giam_gia ==0 
                    ? "{$maGiamGia->so_giam_gia} VNĐ" 
                    : "{$maGiamGia->so_giam_gia}%";

                return [
                    'Mã giảm giá' => $maGiamGia->code,
                    'Loại giảm giá' => $loaiGiamGia,
                    'Giá trị' => $giaTri,
                    'Số tiền tối đa' => "{$maGiamGia->so_tien_toi_da} VNĐ",
                    'Điều kiện tối thiểu đơn hàng' => "{$maGiamGia->dk_toi_thieu_don_hang} VNĐ",
                    'Ngày bắt đầu' => $maGiamGia->ngay_bat_dau,
                    'Ngày kết thúc' => $maGiamGia->ngay_ket_thuc,
                ];
            });

            // Trả dữ liệu dạng JSON
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã xảy ra lỗi',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getAllNhanVien()
    {
        try {
            $nhanViens = \App\Models\NhanVien::select(
                'ho_va_ten',
                'gioi_tinh',
                'tuoi_nhan_vien',
                'kinh_nghiem'
            )->get();
    
            // Biến dữ liệu thành mảng JSON
            $result = $nhanViens->map(function ($nhanVien) {
                $gioiTinh = match ($nhanVien->gioi_tinh) {
                    0 => 'Nữ',
                    1 => 'Nam',
                    2 => 'Khác',
                    default => 'Không xác định'
                };
    
                return [
                    'Tên' => $nhanVien->ho_va_ten,
                    'Giới tính' => $gioiTinh,
                    'Tuổi' => $nhanVien->tuoi_nhan_vien,
                    'Kinh nghiệm' => "{$nhanVien->kinh_nghiem} năm"
                ];
            });
    
            // Chuyển đổi mảng thành chuỗi JSON
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã xảy ra lỗi',
                'message' => $e->getMessage()
            ], 500);
        }
    }
        public function getAllDanhMucDichVu()
    {
        try {
            // Lọc chỉ các mục có trạng thái 'is_active' là true (hoạt động)
            $danhMucs = DanhMucDichVu::select(
                'ten_muc',
                'so_tien',
                'is_active'
            )->where('is_active', 1)->get(); // Lọc ra các mục hoạt động

            $result = $danhMucs->map(function ($danhMuc) {
                return [
                    'Tên mục' => $danhMuc->ten_muc,
                    'Giá' => "{$danhMuc->so_tien} VNĐ",
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã xảy ra lỗi',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function chat(Request $request)
    {
        // Kiểm tra nội dung yêu cầu
        $UserInput = $request->input('message');
        $userInput = strtolower($UserInput);

        if (!$userInput) {
            return response()->json(['error' => 'Message is required'], 400);
        }

        try {
            $additionalData = '';

            $responses = [
                'giờ làm việc' => 'Giờ làm việc của chúng tôi là từ 8h sáng đến 6h chiều từ Thứ 2 đến Thứ 7. Chủ nhật nghỉ.',
                'địa chỉ công ty' => 'Công ty HomeHero tọa lạc tại 123 Đường Chính, Đà Nẵng, Việt Nam',
                'số hotline' => 'Hotline của chúng tôi là +84 123 456 789. Vui lòng gọi trong giờ hành chính để được hỗ trợ.',
                'liên hệ' => 'Hotline của chúng tôi là +84 123 456 789 . Vui lòng gọi trong giờ hành chính để được hỗ trợ.',
                'số điện thoại' => 'Hotline của chúng tôi là +84 123 456 789 . Vui lòng gọi trong giờ hành chính để được hỗ trợ.',
                'đặt lịch' => 'Bước 1 Chọn Dịch Vụ:
                                Chúng tôi có nhiều dịch vụ tiến hành hỗ trợ bạn và đồng hành với bạn 
                                Bước 2 Chọn Thời Gian Và Vị Trí
                                Xác định ngày, giờ và địa điểm để đặt dịch vụ HomeHero trong vòng chưa đầy 60 giây
                                Bước 3 Tiến Hành Công Việc
                                Người giúp việc gia đình/đối tác sẽ xác nhận đến nhà bạn như đã hẹn và thực hiện nhiệm vụ.
                                Bước 4 Đánh Giá và Xếp Hạng
                                Bạn có thể đánh giá chất lượng dịch vụ thông qua ứng dụng HomeHero.',
                'đặt dịch vụ' => 'Bước 1 Chọn Dịch Vụ:
                                Chúng tôi có nhiều dịch vụ tiến hành hỗ trợ bạn và đồng hành với bạn 
                                Bước 2 Chọn Thời Gian Và Vị Trí
                                Xác định ngày, giờ và địa điểm để đặt dịch vụ HomeHero trong vòng chưa đầy 60 giây
                                Bước 3 Tiến Hành Công Việc
                                Người giúp việc gia đình/đối tác sẽ xác nhận đến nhà bạn như đã hẹn và thực hiện nhiệm vụ.
                                Bước 4 Đánh Giá và Xếp Hạng
                                Bạn có thể đánh giá chất lượng dịch vụ thông qua ứng dụng HomeHero.',
                'thanh toán' => 'Chúng tôi cung cấp 2 phương thức thanh toán là sử dụng tiền mặt để thanh toán và chuyển khoản qua ngân hàng.',
                
            ];
    
            // Tìm câu trả lời phù hợp
            foreach ($responses as $keyword => $reply) {
                if (str_contains($userInput, $keyword)) {
                    return response()->json(['reply' => $reply]);
                }
            }
    

           
            

            if (str_contains($userInput, 'nhân viên') || 
            str_contains($userInput, 'người giúp việc')||
            str_contains($userInput, 'nhân sự')) {
                $additionalData = $this->getAllNhanVien()."dựa trên đoạn json để trả lời theo format : hệ thống HomeHero + câu trả lời ";
            }else if(str_contains($userInput, 'dịch vụ') || 
            str_contains($userInput, 'danh mục') || 
            str_contains($userInput, 'sản phẩm')) {
                $additionalData = $this->getAllDanhMucDichVu()."dựa trên đoạn json để trả lời theo format : hệ thống HomeHero + câu trả lời ";
            }else if (str_contains($userInput, 'giảm giá') || 
            str_contains($userInput, 'khuyến mãi') || 
            str_contains($userInput, 'voucher')) {
                $additionalData = $this->getAllMaGiamGia()."dựa trên đoạn json để trả lời theo format : hệ thống HomeHero + câu trả lời ";
            }
            else if(str_contains($userInput, 'giới thiệu') || 
            str_contains($userInput, 'thông tin')||
            str_contains($userInput, 'mô tả')||
            str_contains($userInput, 'công ty')
            ){
                return response()->json([
                    "reply" => "HomeHero là nền tảng thông minh kết nối khách hàng với đội ngũ nhân viên dịch vụ gia đình chuyên nghiệp. 
                    Hệ thống giúp bạn dễ dàng tìm kiếm, đặt lịch và quản lý các dịch vụ như giúp việc, chăm sóc trẻ, và sửa chữa. 
                    Với HomeHero, cuộc sống của bạn trở nên tiện lợi và thoải mái hơn bao giờ hết!"
                ]);
                
            }
            $client = new \GuzzleHttp\Client([
                'timeout' => 9, // Set timeout to 9 seconds
            ]);
            // Gửi yêu cầu tới Gemini AI
            $response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . env('GEMINI_API_KEY'), [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $userInput . $additionalData . " Ngắn gọn"]
                            ]
                        ]
                    ]
                ]
            ]);
            // Kiểm tra phản hồi
            $fullResponse = json_decode($response->getBody()->getContents(), true);
        if (isset($fullResponse['candidates'][0]['content']['parts'][0]['text'])) {
            $reply = $fullResponse['candidates'][0]['content']['parts'][0]['text'];
            return response()->json(['reply' => $reply]);
        }

        return response()->json(['error' => 'No reply found in the response'], 500);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Nếu quá 9 giây hoặc gặp lỗi kết nối
            return response()->json([
                'error' => 'Request timed out',
                'message' => 'Chúng tôi cần mạng internet tốt hơn để trả lời câu hỏi này'
            ], 500);
        } catch (\Exception $e) {
            // Xử lý lỗi không mong muốn
            return response()->json([
                'error' => 'Unexpected error occurred',
                'message' => "Chúng tôi cần mạng internet mạnh để trả lời được câu hỏi này",
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
