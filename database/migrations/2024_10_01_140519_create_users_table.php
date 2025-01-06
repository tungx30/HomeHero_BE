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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Khóa chính
            $table->string('name'); // Tên người dùng
            $table->string('email')->unique(); // Email người dùng (phải duy nhất)
            $table->string('password'); // Mật khẩu người dùng
            $table->timestamp('email_verified_at')->nullable(); // Thời gian xác minh email
            $table->rememberToken(); // Token nhớ đăng nhập
            $table->timestamps(); // Thời gian tạo và cập nhật
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
