<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); // ID chính
            $table->unsignedBigInteger('nguoi_gui_id'); // Người gửi
            $table->unsignedBigInteger('nguoi_nhan_id'); // Người nhận
            $table->text('noi_dung'); // Nội dung tin nhắn
            $table->integer('sender_type')->default(1)->comment("1 là người dùng , 2 là admin");
            $table->timestamps(); // Thời gian gửi
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
