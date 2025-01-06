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
        Schema::create('nhan_viens', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('ho_va_ten');
            $table->string('hinh_anh');
            $table->string('can_cuoc_cong_dan')->unique();
            $table->date('ngay_sinh');
            $table->integer('gioi_tinh')->default(0)->comment('Giới tính của admin: 0 - Nữ, 1 - Nam , 2 - khác');
            $table->string('so_dien_thoai')->unique();
            $table->string('dia_chi')->nullable();
            $table->integer('tuoi_nhan_vien')->default(18);
            $table->string('kinh_nghiem');
            $table->integer('tinh_trang')->comment('Tình trạng hoạt động của nhân viên: 0 - Không online ở trong hệ thống, 1 - Đang online ở trong hệ thống')->default(0);
            $table->integer('is_noi_bat')->default(0)->comment('Nổi bật: 0 - sẽ về trạng thái bình thường, 1 - Nhân Viên nổi bật');
            $table->integer('is_flash_sale')->default(0)->comment('Nổi bật: 0 - sẽ về trạng thái bình thường, 1 - Nhân Viên đang sale');
            $table->integer('is_master')->default(0)->comment('Quyền của admin: 1 , nhân viên hoặc người dùng là: 0');
            $table->integer('id_quyen')->nullable();
            $table->decimal('so_du_vi', 15, 2)->default(0);
            $table->string('hash_reset')->nullable();
            $table->integer('is_block')->default(0)->comment('dùng để admin có thể chặn nhân viên vi phạm: 0 - Không chặn, 1 - Đang chặn');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nhan_viens');
    }
};
