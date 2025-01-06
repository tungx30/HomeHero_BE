<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thong_baos', function (Blueprint $table) {
            $table->decimal('so_tien_rut', 15, 2)->nullable()->after('status')->comment('Số tiền rút được liên kết với thông báo nếu có');
        });
    }

    public function down(): void
    {
        // Schema::table('thong_baos', function (Blueprint $table) {
        //     $table->dropColumn('so_tien_rut');
        // });
    }
};

