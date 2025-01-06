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
        Schema::table('giao_diches', function (Blueprint $table) {
            $table->unsignedBigInteger('id_thong_bao')->nullable()->after('type')->comment('Liên kết với thông báo rút tiền');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('giao_dichs', function (Blueprint $table) {
            //
        });
    }
};
