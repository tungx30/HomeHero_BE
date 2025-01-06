<?php

namespace App\Events;

use App\Models\ThongBao;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThongBaoEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $thongBao;

    /**
     * Create a new event instance.
     *
     * @param ThongBao $thongBao
     */
    public function __construct(ThongBao $thongBao)
    {
        $this->thongBao = $thongBao;
    }

    /**
     * Kênh để phát sóng thông báo.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        // Tạo channel dựa trên ID người nhận
        return new Channel('notifications.' . $this->thongBao->id_nguoi_nhan);
    }

    /**
     * Tên sự kiện để client lắng nghe.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'thongbao.created';
    }
}
