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
        Schema::table('chi_tiet_lich_lams', function (Blueprint $table) {
             // Xóa ràng buộc khóa ngoại nếu có
             $table->dropForeign(['nhan_vien_id']);
             // Đổi kiểu dữ liệu cột `nhan_vien_id` thành TEXT để lưu JSON hoặc chuỗi
             $table->text('nhan_vien_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chi_tiet_lich_lams', function (Blueprint $table) {
            //
        });
    }
};
