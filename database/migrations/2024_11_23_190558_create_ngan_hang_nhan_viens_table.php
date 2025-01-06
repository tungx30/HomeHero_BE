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
        Schema::create('ngan_hang_nhan_viens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nhan_vien_id')->unique();
            $table->foreign('nhan_vien_id')->references('id')->on('nhan_viens')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('stk')->unique();
            $table->string('ten_ngan_hang');
            $table->longText('qrRut')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ngan_hang_nhan_viens');
    }
};
