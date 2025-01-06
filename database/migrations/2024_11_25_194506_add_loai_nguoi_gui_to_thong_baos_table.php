<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('thong_baos', function (Blueprint $table) {
            $table->tinyInteger('loai_nguoi_gui')->after('id_nguoi_gui')->comment('1: Nhân viên, 2: Người dùng , 3: Admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('thong_baos', function (Blueprint $table) {
        //     $table->dropColumn('loai_nguoi_gui');
        // });
    }
};
