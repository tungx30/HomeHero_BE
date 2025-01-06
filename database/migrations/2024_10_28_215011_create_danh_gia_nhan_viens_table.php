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
        Schema::create('danh_gia_nhan_viens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nhan_vien_id')->constrained('nhan_viens')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('don_hang_id')->constrained('don_hangs')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedTinyInteger('so_sao')->comment('Số sao đánh giá, từ 1 đến 5');
            $table->text('nhan_xet')->nullable()->comment('Nhận xét của người dùng');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danh_gia_nhan_viens');
    }
};
