<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            if (!Schema::hasColumn('don_hangs', 'is_da_tinh_luong')) {
                $table->json('is_da_tinh_luong')->nullable()
                    ->comment('Trạng thái tính lương của từng nhân viên trong đơn hàng (dạng JSON: {"nhan_vien_id": 0|1})');
            }
        });
    }

    public function down()
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            if (Schema::hasColumn('don_hangs', 'is_da_tinh_luong')) {
                $table->dropColumn('is_da_tinh_luong');
            }
        });
    }
};

