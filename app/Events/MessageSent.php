<?php

namespace App\Events;

use App\Models\messages;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    protected $message;
    protected $nguoi_gui_avatar;

    public function __construct(messages $message)
    {
        $this->message = $message; // Giữ nguyên model

        if ($message->sender_type == 2) {
            $admin = \App\Models\Admin::find($message->nguoi_gui_id);
            $this->nguoi_gui_avatar = $admin ? $admin->hinh_anh : null;
        } else {
            $user = \App\Models\NguoiDung::find($message->nguoi_gui_id);
            $this->nguoi_gui_avatar = $user ? $user->hinh_anh : null;
        }
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->message->nguoi_nhan_id);
    }

    public function broadcastAs()
    {
        return 'message-sent-event';
    }

    public function broadcastWith()
    {
        // Lấy dữ liệu từ model $this->message, kết hợp với $this->nguoi_gui_avatar
        return [
            'id' => $this->message->id,
            'nguoi_gui_id' => $this->message->nguoi_gui_id,
            'nguoi_nhan_id' => $this->message->nguoi_nhan_id,
            'noi_dung' => $this->message->noi_dung,
            'sender_type' => $this->message->sender_type,
            'created_at' => $this->message->created_at,
            'updated_at' => $this->message->updated_at,
            'nguoi_gui_avatar' => $this->nguoi_gui_avatar,
        ];
    }
}

