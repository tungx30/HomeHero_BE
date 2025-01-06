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
        Schema::create('nguoi_dungs', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('ho_va_ten');
            $table->string('hinh_anh')->nullable();
            $table->date('ngay_sinh');
            $table->integer('gioi_tinh')->default(0)->comment('Giới tính của admin: 0 - Nữ, 1 - Nam , 2 - khác');
            $table->string('so_dien_thoai')->unique();
            $table->integer('tinh_trang')->comment('Tình trạng hoạt động của người dùng: 0 - Không online ở trong hệ thống, 1 - Đang online ở trong hệ thống')->default(0);
            $table->string('hash_reset')->nullable()->comment('dùng để reset mk = gmail');
            $table->string('hash_active')->nullable()->comment('dùng để kích hoạt tk = gmail');
            $table->integer('is_active')->default(0)->comment('dùng để kích hoạt tk thủ công dành cho admin ; 1 là đã kích hoạt,0 là chưa kích hoạt');
            $table->integer('is_block')->default(0)->comment('dùng để chặn tk thủ công dành cho admin ; 1 là đã bị chặn,0 là chưa bị chặn');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nguoi_dungs');
    }
};
