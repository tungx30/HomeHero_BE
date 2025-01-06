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
        Schema::create('vi_dien_tus', function (Blueprint $table) {
            $table->id();
            $table->integer('so_tien_rut')->default(0);
            $table->integer('so_du')->default(0);
            $table->unsignedBigInteger('nhan_vien_id');
            $table->foreign('nhan_vien_id')->references('id')->on('nhan_viens')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('tinh_trang')->comment('tình trạng là ví điện tử đã cộng tiền thành công hay chưa (0 là chưa thành công , 1 là đã thành công')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vi_dien_tus');
    }
};
