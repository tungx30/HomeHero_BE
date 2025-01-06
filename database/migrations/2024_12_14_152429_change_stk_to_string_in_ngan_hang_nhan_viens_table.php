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
        Schema::table('ngan_hang_nhan_viens', function (Blueprint $table) {
            // Xóa ràng buộc UNIQUE trước khi thay đổi kiểu dữ liệu
            $table->dropUnique('ngan_hang_nhan_viens_stk_unique');

            // Thay đổi kiểu dữ liệu thành string và thêm lại ràng buộc UNIQUE
            $table->string('stk')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ngan_hang_nhan_viens', function (Blueprint $table) {
            //
        });
    }
};
