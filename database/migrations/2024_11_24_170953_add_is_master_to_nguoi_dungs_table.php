<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nguoi_dungs', function (Blueprint $table) {
            $table->integer('is_master')
                ->default(0)
                ->comment('Quyền của admin: 1 , nhân viên hoặc người dùng là: 0')
                ->after('is_block'); // Thêm sau cột is_block
        });
    }

    public function down(): void
    {
        // Schema::table('nguoi_dungs', function (Blueprint $table) {
        //     $table->dropColumn('is_master');
        // });
    }
};
