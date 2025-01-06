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
        Schema::create('ma_giam_gias', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('tinh_trang')->comment('Tình trạng hoạt động của mã giảm giá: 0 - Không được áp dụng , 1 - Được áp dụng')->default(0);
            $table->date('ngay_bat_dau');
            $table->date('ngay_ket_thuc');
            $table->integer('loai_giam_gia')->comment("1: Giảm theo %, 0: Giảm theo số tiền");
            $table->integer('so_giam_gia')->comment("Thể hiện số tiền hoặc % mình sẽ giảm giá");
            $table->integer('so_tien_toi_da')->comment("Là dù cho giảm 50% nhưng mà nếu đơn hàng có 1 triệu nhưng giảm tối đa 100k thì vẫn chỉ là giảm 100k");
            $table->integer('dk_toi_thieu_don_hang')->comment("Là điều kiện của đơn hàng, VD:đơn hàng này phải có giá trị tối thiểu là 100k mới được giảm giá");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ma_giam_gias');
    }
};
