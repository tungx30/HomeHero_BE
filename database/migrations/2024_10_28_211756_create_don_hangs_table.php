<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy các migration.
     */
    public function up(): void
    {
        Schema::create('don_hangs', function (Blueprint $table) {
            $table->id();
            $table->string('ma_don_hang')->unique();
            $table->foreignId('id_dich_vu')->constrained('lua_chon_dich_vus')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('nhan_vien_id')->nullable();
            $table->foreign('nhan_vien_id')->references('id')->on('nhan_viens')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_dia_chi')->constrained('dia_chi_nguoi_dungs')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('so_luong_nv')->default(1)->comment('Số lượng nhân viên phục vụ, mặc định là 1');
            $table->integer('so_tang_phuc_vu')->nullable()->comment('Số tầng phục vụ nếu là dịch vụ tổng vệ sinh');
            $table->decimal('so_gio_phuc_vu', 4, 2)->comment('Số giờ phục vụ (2 -> 4h, có thể là 2.5h, 3.5h)');
            $table->time('gio_bat_dau_lam_viec')->comment('số giờ bắt đầu sẽ là từ 8h và sẽ được đặt theo số giờ phục vụ , VD: số giờ phục vụ là 2,5h và số giờ bắt đầu là 8h thì giờ kết thúc sẽ tự tính ra là 10h30.');
            $table->time('gio_ket_thuc_lam_viec')->comment('số giờ kết thúc sẽ là 21h và sẽ được đặt theo số giờ phục vụ , VD: số giờ phục vụ là 2,5h và số giờ bắt đầu là 8h thì giờ kết thúc sẽ tự tính ra là 10h30.');
            $table->json('so_ngay_phuc_vu_hang_tuan')->nullable()->comment('Ngày phục vụ hàng tuần (T2 -> CN) , 1 là T2 , 2 là T3 , 3 là T4 , 4 là T5 , 5 là T6 , 6 là T7 , 0 là CN và khách hàng có thể chọn nhiều ngày làm trong 1 tuần');
            $table->date('ngay_bat_dau_lam')->comment('Ngày mà nhân viên bắt đầu sẽ làm dịch vụ (tùy vào khách hàng chọn ngày nào)');
            $table->integer('tong_so_buoi_phuc_vu_theo_so_thang_phuc_vu')->nullable()->comment('tổng số buổi phục vụ sẽ = số ngày phục vụ hàng tuần * số tháng phục vụ (mặc định là 1 tháng sẽ có 4 tuần');
            $table->integer('so_thang_phuc_vu')->nullable()->comment('Số tháng sẽ phục vụ và mặc định 1 tháng có 4 tuần');
            $table->string('loai_nha')->nullable()->comment('Loại nhà : 1.căn hộ , 2.nhà mặt đất , 3.biệt thự , 4.văn phòng -> loại nhà này để khi hiển thị ra cho nhân viên biết mà dọn dẹp');
            $table->float('dien_tich_tong_san')->nullable()->comment('Diện tích tổng sản dành cho tổng vệ sinh , 6 lựa chọn-> value = 1:dưới 60m2 tổng sàn + 2 nhân viên; value = 1,5:60-90m2 + 3 nhân viên; value = 2: trên 90m2-120m2 + 4 nhân viên; value = 2,5:120-150m2 + 5 nhân viên; value = 3:150-180m2 + 6 nhân viên ; value = 3,5:180-210m2 + 7 nhân viên');
            $table->integer('tong_tien');
            $table->string('ma_code_giam')->nullable();
            $table->integer('so_tien_giam')->default(0);
            $table->integer('so_tien_thanh_toan');
            $table->tinyInteger('is_thanh_toan')->default(0)->comment('0 là chưa thanh toán , 1 là đã thanh toán rồi');
            $table->integer('tinh_trang_don_hang')->default(0)->comment('Tình trạng đơn hàng: 0 - chưa đặt dịch vụ , 1 - Đã đặt dịch vụ - Đang xử lý (đợi nhân viên nhận đơn), 2 - Đã nhận đơn , 3 - Đã hoàn thành , 4- Đã hủy đơn dịch vụ');
            $table->text('ghi_chu')->nullable()->comment('Ghi chú của người dùng');
            $table->tinyInteger('phuong_thuc_thanh_toan')->comment('0: Thanh toán online, 1: Thanh toán tiền mặt , 2: Thanh toán bằng ví điện tử');
            $table->timestamps();
        });
    }

    /**
     * Hoàn tác các migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('don_hangs');
    }
};
