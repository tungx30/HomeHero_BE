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
        Schema::create('chi_tiet_lich_lams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nhan_vien_id')->nullable();
            $table->foreign('nhan_vien_id')->references('id')->on('nhan_viens')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('don_hang_id')->constrained('don_hangs')->onDelete('cascade')->onUpdate('cascade');
            $table->date('ngay_lam_viec')->comment('Ngày làm hàng tuần (T2 -> CN) , 1 là T2 , 2 là T3 , 3 là T4 , 4 là T5 , 5 là T6 , 6 là T7 , 0 là CN và nhân viên sẽ có nhiều ngày làm việc vì sẽ nhận nhiều đơn của khách hàng');
            $table->decimal('so_gio_phuc_vu', 4, 2)->comment('Số giờ phục vụ (2 -> 4h, có thể là 2.5h, 3.5h)');
            $table->time('gio_bat_dau')->comment('số giờ bắt đầu sẽ là từ 8h và sẽ được đặt theo số giờ phục vụ , VD: số giờ phục vụ là 2,5h và số giờ bắt đầu là 8h thì giờ kết thúc sẽ tự tính ra là 10h30.');
            $table->time('gio_ket_thuc')->comment('số giờ kết thúc sẽ là 21h và sẽ được đặt theo số giờ phục vụ , VD: số giờ phục vụ là 2,5h và số giờ bắt đầu là 8h thì giờ kết thúc sẽ tự tính ra là 10h30.');
            $table->integer('is_active')->comment('trạng thái lịch làm này đã xong hay chưa (0 là đang làm , 1 là đã xong)')->default(0);
            $table->integer('is_nhan_lich')->comment('trạng thái lịch làm này đã được nhân viên nhận hay chưa (0 là chưa nhận lịch , 1 là đã nhận lịch)')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_lich_lams');
    }
};
