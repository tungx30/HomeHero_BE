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
        Schema::create('lua_chon_dich_vus', function (Blueprint $table) {
            $table->id();
            $table->string('ten_lua_chon')->comment('tên lựa chọn dịch vụ, ví dụ Thuê Theo Giờ, Thuê theo định kì')->unique();
            // Khóa ngoại tham chiếu đến id của bảng danh_muc_dich_vus
            $table->string('slug_dich_vu')->unique();
            $table->string('icon_dich_vu');
            $table->foreignId('id_muc')
                  ->constrained('danh_muc_dich_vus') // Tham chiếu đến cột id của bảng danh_muc_dich_vus
                  ->onDelete('cascade') // Xóa bản ghi nếu mục dịch vụ bị xóa
                  ->onUpdate('cascade'); // Cập nhật bản ghi nếu id mục dịch vụ thay đổi
            $table->integer('is_active')->comment('0 là off, 1 là onl')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lua_chon_dich_vus');
    }
};
