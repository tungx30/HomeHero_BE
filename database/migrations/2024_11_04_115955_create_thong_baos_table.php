<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('thong_baos', function (Blueprint $table) {
            $table->id();
            $table->string('loi_nhan');
            $table->integer('id_nguoi_gui')->comment('id của người gửi thông báo ví dụ như là id_khách hàng hay là id của nhân viên , admin');
            $table->integer('id_don_hang')->nullable();
            $table->integer('id_nguoi_nhan')->comment('id của người nhận thông báo ví dụ như là id_khách hàng hay là id của nhân viên , admin')->nullable();
            $table->integer('types')->comment('1->thông báo này là của khách hàng , 2->thông báo này là của nhân viên , 3->thông báo này là của admin');
            $table->integer('status')->default(0)->comment('tình trạng của thông báo này là đã được gửi hay là chưa (1 là đã gửi , 0 là chưa gửi) ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thong_baos');
    }
};
