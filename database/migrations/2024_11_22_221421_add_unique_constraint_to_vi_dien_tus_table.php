<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueConstraintToViDienTusTable extends Migration
{
    public function up()
    {
        Schema::table('vi_dien_tus', function (Blueprint $table) {
            $table->unique('nhan_vien_id'); // Thêm ràng buộc UNIQUE
        });
    }
    public function down()
    {
        Schema::table('vi_dien_tus', function (Blueprint $table) {
            $table->dropUnique(['nhan_vien_id']); // Xóa ràng buộc UNIQUE
        });
    }
}
