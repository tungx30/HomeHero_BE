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
        Schema::create('dia_chi_nguoi_dungs', function (Blueprint $table) {
            $table->id();
            $table->string('dia_chi')->comment('Địa chỉ người dùng');
            $table->string('ten_nguoi_nhan')->comment('Tên người nhận dịch vụ');
            $table->string('so_dien_thoai')->comment('Số điện thoại liên hệ');
            $table->foreignId('id_nguoi_dung')
                  ->constrained('nguoi_dungs') // Tham chiếu đến bảng nguoi_dungs
                  ->onDelete('cascade')  // Xóa địa chỉ khi người dùng bị xóa
                  ->onUpdate('cascade'); // Cập nhật nếu ID người dùng thay đổi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dia_chi_nguoi_dungs');
    }
};
