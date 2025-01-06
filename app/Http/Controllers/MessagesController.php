<?php

namespace App\Http\Controllers;

use App\Models\messages;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessagesController extends Controller
{
    public function sendMessageByAdmin(Request $request)
    {
        $tai_khoan_dang_dang_nhap = Auth::guard('sanctum')->user();
        $isAdmin = DB::table('admins')->where('id', $tai_khoan_dang_dang_nhap->id)->exists();
        if (!$isAdmin) {
            return response()->json(['error' => 'Tài khoản không hợp lệ'], 403);
        }
        $message = messages::create([
            'nguoi_gui_id' => $tai_khoan_dang_dang_nhap->id,
            'nguoi_nhan_id' => $request->nguoi_nhan_id,
            'noi_dung' => $request->noi_dung,
            'sender_type' => 2,
        ]);
        broadcast(new MessageSent($message));
        return response()->json(['message' => 'Tin nhắn đã được gửi', 'data' => $message], 201);
    }


    public function getMessageByAdmin(Request $request)
    {
        $tai_khoan_dang_dang_nhap =  Auth::guard('sanctum')->user();
        $adminId = $tai_khoan_dang_dang_nhap->id;
        // Lấy tất cả tin nhắn gửi đến admin
        $users = DB::table('messages')
            ->join('nguoi_dungs', 'messages.nguoi_gui_id', '=', 'nguoi_dungs.id')
            ->where('messages.nguoi_nhan_id', $adminId)
            ->select(
                'nguoi_dungs.id as nguoi_gui_id',
                'nguoi_dungs.ho_va_ten as nguoi_gui_ten',
                'nguoi_dungs.hinh_anh as nguoi_gui_avatar',
                DB::raw('MAX(messages.created_at) as latest_time'),
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(messages.noi_dung ORDER BY messages.created_at DESC), ",", 1) as latest_message')
            )
            ->groupBy('nguoi_dungs.id', 'nguoi_dungs.ho_va_ten', 'nguoi_dungs.hinh_anh')
            ->orderBy('latest_time', 'DESC')
            ->get();

        return response()->json($users);
    }

    public function sendMessageByUser(Request $request)
    {
        $tai_khoan_dang_dang_nhap = Auth::guard('sanctum')->user();
        $isUser = DB::table('nguoi_dungs')->where('id', $tai_khoan_dang_dang_nhap->id)->exists();
        if (!$isUser) {
            return response()->json(['error' => 'Tài khoản không hợp lệ'], 403);
        }
        // Tạo tin nhắn
        $message = messages::create([
            'nguoi_gui_id' => $tai_khoan_dang_dang_nhap->id, // ID của user
            'nguoi_nhan_id' => $request->nguoi_nhan_id,      // ID của người nhận
            'noi_dung' => $request->noi_dung,               // Nội dung tin nhắn
        ]);
        // Phát sự kiện
        broadcast(new MessageSent($message));
        return response()->json(['message' => 'Tin nhắn đã được gửi', 'data' => $message], 201);
    }


    // Nhận tin nhắn cho Người dùng
    public function getMessageByUser(Request $request)
    {
        $tai_khoan_dang_dang_nhap =  Auth::guard('sanctum')->user();
        $userId = $tai_khoan_dang_dang_nhap->id;
        // Lấy tất cả tin nhắn gửi đến người dùng
        $messages = messages::where('nguoi_nhan_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
    public function getChiTietTinNhan(Request $request)
    {
        $tai_khoan_dang_dang_nhap =  Auth::guard('sanctum')->user();
        $adminId = $tai_khoan_dang_dang_nhap->id;

        $messages = messages::select(
                'messages.*',
                'nguoi_gui_dungs.id as nguoi_gui_id',
                'nguoi_gui_dungs.ho_va_ten as nguoi_gui_ten',
                'nguoi_gui_dungs.hinh_anh as nguoi_gui_avatar',
                'nguoi_gui_dungs.tinh_trang as tinh_trang_nguoi_gui',
                'nguoi_gui_admins.id as nguoi_gui_admin_id',
                'nguoi_gui_admins.ho_va_ten as nguoi_gui_admin_ten',
                'nguoi_gui_admins.hinh_anh as nguoi_gui_admin_avatar',
                'nguoi_nhan_dungs.id as nguoi_nhan_id',
                'nguoi_nhan_dungs.ho_va_ten as nguoi_nhan_ten',
                'nguoi_nhan_dungs.hinh_anh as nguoi_nhan_avatar',
                'nguoi_nhan_admins.id as nguoi_nhan_admin_id',
                'nguoi_nhan_admins.ho_va_ten as nguoi_nhan_admin_ten',
                'nguoi_nhan_admins.hinh_anh as nguoi_nhan_admin_avatar'
            )
            ->leftJoin('nguoi_dungs as nguoi_gui_dungs', 'messages.nguoi_gui_id', '=', 'nguoi_gui_dungs.id')
            ->leftJoin('admins as nguoi_gui_admins', 'messages.nguoi_gui_id', '=', 'nguoi_gui_admins.id')
            ->leftJoin('nguoi_dungs as nguoi_nhan_dungs', 'messages.nguoi_nhan_id', '=', 'nguoi_nhan_dungs.id')
            ->leftJoin('admins as nguoi_nhan_admins', 'messages.nguoi_nhan_id', '=', 'nguoi_nhan_admins.id')
            ->where(function ($query) use ($request, $adminId) {
                $query->where('messages.nguoi_gui_id', $request->nguoi_gui_id)
                    ->where('messages.nguoi_nhan_id', $adminId);
            })
            ->orWhere(function ($query) use ($request, $adminId) {
                $query->where('messages.nguoi_gui_id', $adminId)
                    ->where('messages.nguoi_nhan_id', $request->nguoi_gui_id);
            })
            ->orderBy('messages.created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function getChiTietTinNhanNguoiDung(Request $request)
    {
        $tai_khoan_dang_dang_nhap = Auth::guard('sanctum')->user(); // Người dùng đăng nhập
        $nguoiDungId = $tai_khoan_dang_dang_nhap->id; // Lấy ID người dùng hiện tại

        $messages = messages::select(
                'messages.*',
                'nguoi_gui_dungs.id as nguoi_gui_id',
                'nguoi_gui_dungs.ho_va_ten as nguoi_gui_ten',
                'nguoi_gui_admins.id as nguoi_gui_admin_id',
                'nguoi_gui_admins.ho_va_ten as nguoi_gui_admin_ten',
                'nguoi_gui_admins.hinh_anh as nguoi_gui_avatar',
                'nguoi_nhan_dungs.id as nguoi_nhan_id',
                'nguoi_nhan_dungs.ho_va_ten as nguoi_nhan_ten',
                'nguoi_nhan_dungs.hinh_anh as nguoi_nhan_avatar',
                'nguoi_nhan_admins.id as nguoi_nhan_admin_id',
                'nguoi_nhan_admins.ho_va_ten as nguoi_nhan_admin_ten',
                'nguoi_nhan_admins.hinh_anh as nguoi_nhan_admin_avatar'
            )
            ->leftJoin('nguoi_dungs as nguoi_gui_dungs', 'messages.nguoi_gui_id', '=', 'nguoi_gui_dungs.id')
            ->leftJoin('admins as nguoi_gui_admins', 'messages.nguoi_gui_id', '=', 'nguoi_gui_admins.id')
            ->leftJoin('nguoi_dungs as nguoi_nhan_dungs', 'messages.nguoi_nhan_id', '=', 'nguoi_nhan_dungs.id')
            ->leftJoin('admins as nguoi_nhan_admins', 'messages.nguoi_nhan_id', '=', 'nguoi_nhan_admins.id')
            ->where(function ($query) use ($request, $nguoiDungId) {
                $query->where('messages.nguoi_gui_id', $request->nguoi_gui_id)
                    ->where('messages.nguoi_nhan_id', $nguoiDungId); // Người dùng hiện tại là người nhận
            })
            ->orWhere(function ($query) use ($request, $nguoiDungId) {
                $query->where('messages.nguoi_gui_id', $nguoiDungId) // Người dùng hiện tại là người gửi
                    ->where('messages.nguoi_nhan_id', $request->nguoi_gui_id);
            })
            ->orderBy('messages.created_at', 'asc')
            ->get();

        return response()->json($messages);
    }



}
