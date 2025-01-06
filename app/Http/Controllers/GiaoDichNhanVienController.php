<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use App\Mail\MasterMail;
use App\Models\NhanVien;
use App\Models\ThongBao;
use App\Models\ViDienTu;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\GiaoDichNhanVien;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\View\Components\Info;

class GiaoDichNhanVienController extends Controller
{
    public function napTienNhanVien()
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
                $canCuocCongDan = $this->extractCanCuocNhanVien($value['description']);
                if (!$canCuocCongDan) {
                    continue;
                }
                $nhanVien = NhanVien::where('can_cuoc_cong_dan', $canCuocCongDan)->first();
                if (!$nhanVien) {
                    continue;
                }
                $exists = GiaoDichNhanVien::where('refNo', $value['refNo'])->exists();
                if ($exists) {
                    continue;
                }
                GiaoDichNhanVien::create([
                    'creditAmount' => $value['creditAmount'],
                    'debitAmount'  => $value['debitAmount'],
                    'description'  => $value['description'],
                    'refNo'        => $value['refNo'],
                    'nhan_vien_id' => $nhanVien->id,
                    'type'         => 1,
                    'is_done'      => 1,
                ]);

                $viDienTu = ViDienTu::firstOrCreate(
                    ['nhan_vien_id' => $nhanVien->id],
                    ['so_du' => 0]
                );
                $viDienTu->so_du += $value['creditAmount'];
                $viDienTu->tinh_trang = 1;
                $viDienTu->save();
                $x['ho_ten'] = $nhanVien->ho_va_ten;
                $x['so_tien_chuyen_khoan'] = $value['creditAmount'];
                Mail::to($nhanVien->email)->send(new MasterMail('Xác Nhận Nạp Tiền', 'NhanVienNapTien', $x));
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
                    'message' => 'Không có giao dịch mới nào được thực hiện.Vui lòng thực hiện 1 giao dịch mới'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }


    public function extractCanCuocNhanVien($description)
    {
        $description = preg_replace('/\s+/', '', $description);
        if (preg_match('/0\d{11}/', $description, $matches)) {
            return $matches[0];
        }
        return null;
    }
    public function rutTienNhanVien($idThongBao)
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

        $nhanVien = NhanVien::find($thongBao->id_nguoi_gui);
        if (!$nhanVien) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy thông tin nhân viên.'
            ], 404);
        }
        $daXuLyGiaoDich = false;
        $paymentSufficient = false;
        $paymentInsufficient = false;
        $message = '';
        $soTienThieu = 0;
        $descriptionPattern = "NV: {$nhanVien->ho_va_ten}, CCCD: {$nhanVien->can_cuoc_cong_dan}";
        $tongTienDaNhan = GiaoDichNhanVien::where('nhan_vien_id', $nhanVien->id)
            ->where('type', 2)
            ->where('is_done', 0)
            ->where('id_thong_bao', $idThongBao)
            ->sum('debitAmount');

        $soTienRut = $thongBao->so_tien_rut;

        foreach ($data as $value) {
            $exists = GiaoDichNhanVien::where('refNo', $value['refNo'])->exists();
            if ($exists) {
                continue;
            }

            if ($value['creditAmount'] == 0 && $value['debitAmount'] > 0) {
                $soTienThieu = $soTienRut - ($tongTienDaNhan + $value['debitAmount']);
                $giaoDich = GiaoDichNhanVien::create([
                    'creditAmount' => $value['creditAmount'],
                    'debitAmount'  => $value['debitAmount'],
                    'description'  => $descriptionPattern,
                    'refNo'        => $value['refNo'],
                    'nhan_vien_id' => $nhanVien->id,
                    'type'         => 2,
                    'id_thong_bao' => $idThongBao,
                    'is_duyet'     => 1,
                    'is_done'      => ($soTienThieu <= 0) ? 1 : 0,
                    'status'       => ($soTienThieu == 0) ? 1 : 0
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
            GiaoDichNhanVien::where('nhan_vien_id', $nhanVien->id)
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



    public function getData()
    {
        $nhanVien = Auth::guard('sanctum')->user();
        $data = GiaoDichNhanVien::where('nhan_vien_id', $nhanVien->id)->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
    public function getDataNV()
    {
        $data = GiaoDichNhanVien::with(['nhanVien:id,ho_va_ten,can_cuoc_cong_dan'])->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
    public function search(Request $request)
    {
        $nhanVien = Auth::guard('sanctum')->user();
        $noi_dung_tim = '%' . $request->noi_dung_tim . '%';
        $data   =  GiaoDichNhanVien::where('nhan_vien_id', $nhanVien->id)
            ->where('description', 'like', $noi_dung_tim)
            ->orWhere('debitAmount', 'like', $noi_dung_tim)
            ->orWhere('creditAmount', 'like', $noi_dung_tim)
            ->where('is_done', 1)
            ->get();
        return response()->json([
            'data'  => $data
        ]);
    }
    public function searchNV(Request $request)
    {
        $noi_dung_tim = '%' . $request->noi_dung_tim . '%';
        $data = GiaoDichNhanVien::with('nhanVien')
            ->where('is_done', 1)
            ->where(function ($query) use ($noi_dung_tim) {
                $query->where('description', 'like', $noi_dung_tim)
                    ->orWhere('debitAmount', 'like', $noi_dung_tim)
                    ->orWhere('creditAmount', 'like', $noi_dung_tim);
            })
            ->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
