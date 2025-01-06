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
        Schema::create('giao_dich_nhan_viens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nhan_vien_id'); // Thêm cột nhan_vien_id
            $table->integer('creditAmount')->comment('số tiền đã nhận');
            $table->integer('debitAmount')->comment('Số tiền đã chuyển');
            $table->string('description');
            $table->string('refNo')->unique();
            $table->foreign('nhan_vien_id')->references('id')->on('nhan_viens')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('type')->comment('1 là nạp tiền vào , 2 là rút tiền');
            $table->integer('is_duyet')->comment('để cho mỗi khi nhân viên gửi yêu cầu rút tiền (1 là đã duyệt , 0 là chưa )')->default(0);
            $table->integer('is_done')->comment('Tự chuyển đổi nếu check số tiền đã chuyển trùng với số tiền muốn rút ở ví nhân viên (0 là chưa hoàn thành , 1 là đã hoàn thành)')->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giao_dich_nhan_viens');
    }
};
