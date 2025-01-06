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
        Schema::create('vi_dien_tu_nguoi_dungs', function (Blueprint $table) {
            $table->id();
            $table->integer('so_du')->default(0);
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('tinh_trang')->comment('tình trạng là ví điện tử đã cộng tiền thành công hay chưa (0 là chưa thành công , 1 là đã thành công')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('vi_dien_tu_nguoi_dungs');
    }
};
