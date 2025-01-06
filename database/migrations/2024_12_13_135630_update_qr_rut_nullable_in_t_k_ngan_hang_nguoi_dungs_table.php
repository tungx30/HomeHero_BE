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
        Schema::table('t_k_ngan_hang_nguoi_dungs', function (Blueprint $table) { $table->longText('qrRut')->nullable()->change(); });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_k_ngan_hang_nguoi_dungs', function (Blueprint $table) {
            //
        });
    }
};
