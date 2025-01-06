<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\GiaoDich;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\MasterMail;
use App\Models\NguoiDung;
use App\Models\ThongBao;
use App\Models\ViDienTu;
use App\Models\ViDienTuNguoiDung;
use GuzzleHttp\Client;
use Illuminate\Console\View\Components\Info;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GiaoDichController extends Controller
{
    public function index()
    {
        $payload = [
            "USERNAME" => "0369396097",
            "PASSWORD" => "Nt3008@@@",
            "DAY_BEGIN" => Carbon::today()->format('d/m/Y'),
            "DAY_END" => Carbon::today()->format('d/m/Y'),
            "NUMBER_MB" => "0369396097"
        ];

        $client = new Client();
        $response = $client->post("https://api-mb.dzmid.io.vn/mb", [
            'json' => $payload
        ]);
        $data = json_decode($response->getBody(), true)['data'];
        $daXuLyGiaoDich = false;
        $paymentSufficient = false;
        $paymentInsufficient = false;
        $message = '';
        $soTienThieu = 0;

        foreach ($data as $key => $value) {
            if ($value['creditAmount'] > 0) {
                $check = GiaoDich::where('refNo', $value['refNo'])->first();
                if (!$check) {
                    $ma_don_hang = $this->extractMaDonHang($value['description']);
                    $donHang = DonHang::where('ma_don_hang', $ma_don_hang)->first();
                    if ($donHang) {
                        GiaoDich::create([
                            'creditAmount'    => $value['creditAmount'],
                            'debitAmount'     => $value['debitAmount'],
                            'description'     => $value['description'],
                            'refNo'           => $value['refNo'],
                            'id_don_hang'     => $donHang->id,
                            'nguoi_dung_id'   => $donHang->nguoi_dung_id,
                            'is_done'         => 1,
                            'type'            => 3,
                        ]);
                        $daXuLyGiaoDich = true;
                        $nguoiDung = NguoiDung::where('id', $donHang->nguoi_dung_id)->first();
                        $tongTienDaThanhToan = GiaoDich::where('id_don_hang', $donHang->id)->sum('creditAmount');
                        if ($tongTienDaThanhToan >= $donHang->so_tien_thanh_toan) {
                            $donHang->is_thanh_toan = 1;
                            $donHang->save();
                            $x['ho_ten']               = $nguoiDung->ho_va_ten;
                            $x['id_don_hang']          = $donHang->id;
                            $x['so_tien_chuyen_khoan'] = $tongTienDaThanhToan;

                            try {
                                Mail::to($nguoiDung->email)->send(new MasterMail('Xác Nhận Thanh Toán', 'NguoiDungThanhToan', $x));
                            } catch (\Exception $e) {
                                return response()->json([
                                    'status' => true,
                                    'message' => 'Thanh Toán thành công nhưng hệ thống gửi mail đã bị lỗi .Xin quý khách thông cảm cho sự bất tiện này'
                                ]);
                            }

                            $loiNhan = "Người dùng " . $nguoiDung->ho_va_ten . " đã đặt đơn hàng với mã " . $donHang->ma_don_hang . ". Hãy bấm xem chi tiết đơn để xem thông tin chi tiết đơn.";
                            $this->taoThongBaoChoNhanVienNhanDon($loiNhan, $nguoiDung->id, $donHang->id);
                            $paymentSufficient = true;
                            $message = 'Thanh toán thành công, hãy kiểm tra email của bạn. Xin vui lòng đợi ít phút để nhân viên nhận đơn.';
                        } else {
                            $soTienThieu = $donHang->so_tien_thanh_toan - $tongTienDaThanhToan;
                            $x['ho_ten']                 = $nguoiDung->ho_va_ten;
                            $x['id_don_hang']            = $donHang->id;
                            $x['so_tien_chuyen_khoan']   = $tongTienDaThanhToan;
                            $x['so_tien_can_thanh_toan'] = $donHang->so_tien_thanh_toan;
                            $x['tien_thieu']             = $soTienThieu;
                            try {
                                Mail::to($nguoiDung->email)->send(new MasterMail('Thanh Toán Nhưng Thiếu', 'NguoiDungThanhToanThieuTien', $x));
                            } catch (\Exception $e) {
                                return response()->json([
                                    'status' => true,
                                    'message' => 'Thanh Toán thành công và bị thiếu ' . $soTienThieu . 'VNĐ nhưng hệ thống gửi mail đã bị lỗi .Xin quý khách thông cảm cho sự bất tiện này'
                                ]);
                            }
                            $paymentInsufficient = true;
                            $message = 'Thanh toán thiếu, số tiền thiếu là ' . $soTienThieu . ' VND.';
                        }
                    }
                }
            }
        }
        if ($paymentSufficient) {
            return response()->json([
                'status' => true,
                'message' => $message,
                'payment_sufficient' => true
            ]);
        } elseif ($paymentInsufficient) {
            return response()->json([
                'status' => false,
                'message' => $message,
                'payment_sufficient' => false,
                'amount_due' => $soTienThieu
            ]);
        } elseif (!$daXuLyGiaoDich) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy giao dịch mới hoặc giao dịch đang hoặc được xử lý. Hãy thử lại sau ít phút nữa.'
            ]);
        }
    }

    public function extractMaDonHang($input)
    {
        $input = preg_replace('/\s+/', ' ', $input);
        if (preg_match('/H\s*o\s*m\s*e\s*H\s*e\s*r\s*o\s*((?:\w+\s*)+)/i', $input, $matches)) {
            $orderPart = $matches[1];
            $orderPart = preg_replace('/\s+/', '', $orderPart);
            $maDonHang = 'HomeHero' . $orderPart;
            return $maDonHang;
        }
        return null;
    }

    public function napTienNguoiDung()
    {
        $payload = [
            "USERNAME" => "0369396097",
            "PASSWORD" => "Nt3008@@@",
            "DAY_BEGIN" => Carbon::today()->format('d/m/Y'),
            "DAY_END" => Carbon::today()->format('d/m/Y'),
            "NUMBER_MB" => "0369396097"
        ];
        $hasSuccessfulTransaction = false;
        try {
            $client = new Client();
            $response = $client->post("https://api-mb.dzmid.io.vn/mb", [
                'json' => $payload
            ]);
            $data = json_decode($response->getBody(), true)['data'];

            if (!isset($data) || empty($data)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không có dữ liệu từ API ngân hàng.'
                ], 400);
            }
            foreach ($data as $value) {
                if (!is_numeric($value['creditAmount']) || $value['creditAmount'] <= 0 || $value['debitAmount'] > 0) {
                    continue;
                }
                $soDienThoai = $this->extractSĐTNguoiDung($value['description']);
                if (!$soDienThoai) {
                    continue;
                }
                $nguoiDung = NguoiDung::where('so_dien_thoai', $soDienThoai)->first();
                if (!$nguoiDung) {
                    continue;
                }
                $exists = GiaoDich::where('refNo', $value['refNo'])->exists();
                if ($exists) {
                    continue;
                }
                GiaoDich::create([
                    'creditAmount' => $value['creditAmount'],
                    'debitAmount'  => $value['debitAmount'],
                    'description'  => $value['description'],
                    'refNo'        => $value['refNo'],
                    'nguoi_dung_id' => $nguoiDung->id,
                    'is_done'       => 1,
                    'type'         => 1
                ]);

                $viDienTu = ViDienTuNguoiDung::firstOrCreate(
                    ['nguoi_dung_id' => $nguoiDung->id],
                    ['so_du' => 0]
                );
                $viDienTu->so_du += $value['creditAmount'];
                $viDienTu->save();

                $x['ho_ten'] = $nguoiDung->ho_va_ten;
                $x['so_tien_chuyen_khoan'] = $value['creditAmount'];
                Mail::to($nguoiDung->email)->send(new MasterMail('Xác Nhận Nạp Tiền', 'NguoiDungNapTien', $x));
                $hasSuccessfulTransaction = true;
            }

            if ($hasSuccessfulTransaction) {
                return response()->json([
                    'status' => true,
                    'message' => 'Nạp tiền thành công. Hãy kiểm tra email của bạn.'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Không có giao dịch mới nào được thực hiện. Vui lòng thực hiện 1 giao dịch mới.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    public function rutTienNguoiDung($idThongBao)
    {
        $payload = [
            "USERNAME" => "0369396097",
            "PASSWORD" => "Nt3008@@@",
            "DAY_BEGIN" => Carbon::today()->format('d/m/Y'),
            "DAY_END" => Carbon::today()->format('d/m/Y'),
            "NUMBER_MB" => "0369396097"
        ];

        $client = new Client();
        $response = $client->post("https://api-mb.dzmid.io.vn/mb", [
            'json' => $payload
        ]);

        $data = json_decode($response->getBody(), true)['data'];

        $thongBao = ThongBao::find($idThongBao);
        if (!$thongBao) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy thông báo liên quan.'
            ], 404);
        }

        $nguoiDung = NguoiDung::find($thongBao->id_nguoi_gui);
        if (!$nguoiDung) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy thông tin người dùng.'
            ], 404);
        }
        $daXuLyGiaoDich = false;
        $paymentSufficient = false;
        $paymentInsufficient = false;
        $message = '';
        $soTienThieu = 0;
        $descriptionPattern = "KH {$nguoiDung->ho_va_ten}, SĐT: {$nguoiDung->so_dien_thoai}";
        $tongTienDaNhan = GiaoDich::where('nguoi_dung_id', $nguoiDung->id)
            ->where('type', 2)
            ->where('is_done', 0)
            ->where('id_thong_bao', $idThongBao)
            ->sum('debitAmount');
        $soTienRut = $thongBao->so_tien_rut;
        foreach ($data as $value) {
            $exists = GiaoDich::where('refNo', $value['refNo'])->exists();
            if ($exists) {
                continue;
            }
            if ($value['creditAmount'] == 0 && $value['debitAmount'] > 0) {
                $soTienThieu = $soTienRut - ($tongTienDaNhan + $value['debitAmount']);
                $giaoDich = GiaoDich::create([
                    'creditAmount'  => $value['creditAmount'],
                    'debitAmount'   => $value['debitAmount'],
                    'description'   => $descriptionPattern,
                    'refNo'         => $value['refNo'],
                    'nguoi_dung_id' => $nguoiDung->id,
                    'type'          => 2,
                    'id_thong_bao'  => $idThongBao,
                    'is_duyet'      => 1,
                    'is_done'       => ($soTienThieu <= 0) ? 1 : 0,
                    'status'        => ($soTienThieu == 0) ? 1 : 0
                ]);
                if ($giaoDich) {
                    $tongTienDaNhan += $value['debitAmount'];
                    $daXuLyGiaoDich = true;
                    if ($soTienThieu <= 0) {
                        $paymentSufficient = true;
                        $message = "Rút tiền thành công. Tổng số tiền đã nhận: " . $tongTienDaNhan . " VNĐ.";
                    } else {
                        $paymentInsufficient = true;
                        $message = "Admin đã chuyển thiếu " . abs($soTienThieu) . " VNĐ. Tổng số tiền đã nhận: " . $tongTienDaNhan . " VNĐ.";
                    }
                }
            }
        }
        if ($tongTienDaNhan >= $soTienRut && $paymentSufficient) {
            GiaoDich::where('nguoi_dung_id', $nguoiDung->id)
                ->where('type', 2)
                ->where('id_thong_bao', $idThongBao)
                ->update(['is_done' => 1]);

            $thongBao->delete();
        }
        if (!$daXuLyGiaoDich) {
            return response()->json([
                'status' => false,
                'message' => 'Không có giao dịch mới được xử lý. Vui lòng thử lại sau.',
                'payment_sufficient' => false
            ]);
        }
        if ($paymentSufficient) {
            return response()->json([
                'status' => true,
                'message' => $message,
                'payment_sufficient' => true
            ]);
        } elseif ($paymentInsufficient) {
            return response()->json([
                'status' => false,
                'message' => $message,
                'payment_sufficient' => false,
                'amount_due' => $soTienThieu
            ]);
        }
    }

    public function extractSĐTNguoiDung($description)
    {
        $description = preg_replace('/\s+/', '', $description);
        if (preg_match('/0\d{9}/', $description, $matches)) {
            return $matches[0];
        }
        return null;
    }
    public function getData()
    {
        $khachHang = Auth::guard('sanctum')->user();
        $data = GiaoDich::where('nguoi_dung_id', $khachHang->id)->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
    public function getDataND()
    {
        $data = GiaoDich::with(['nguoiDung:id,ho_va_ten,so_dien_thoai'])->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
    public function search(Request $request)
    {
        $noi_dung_tim = '%' . $request->noi_dung_tim . '%';
        $data   =  GiaoDich::where('description', 'like', $noi_dung_tim)
            ->orWhere('debitAmount', 'like', $noi_dung_tim)
            ->orWhere('creditAmount', 'like', $noi_dung_tim)
            ->where('is_done', 1)
            ->get();
        return response()->json([
            'data'  => $data
        ]);
    }
    public function searchND(Request $request)
    {
        $noi_dung_tim = '%' . $request->noi_dung_tim . '%';
        $data   =  GiaoDich::with(['nguoiDung:id,ho_va_ten,so_dien_thoai'])
            ->where('description', 'like', $noi_dung_tim)
            ->orWhere('debitAmount', 'like', $noi_dung_tim)
            ->orWhere('creditAmount', 'like', $noi_dung_tim)
            ->where('is_done', 1)
            ->get();
        return response()->json([
            'data'  => $data
        ]);
    }
}
