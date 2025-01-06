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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('ho_va_ten');
            $table->date('ngay_sinh')->nullable();
            $table->integer('gioi_tinh')->default(1)->nullable()->comment('Giới tính của admin: 0 - Nữ, 1 - Nam');
            $table->string('so_dien_thoai')->unique();
            $table->string('dia_chi')->nullable();
            $table->integer('tinh_trang')->comment('Tình trạng hoạt động của admin: 0 - Không hoạt động, 1 - Đang hoạt động');
            $table->integer('is_master')->default(1)->comment('Quyền của admin: 1 , nhân viên hoặc người dùng là: 0');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
