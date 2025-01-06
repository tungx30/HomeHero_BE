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
        Schema::create('danh_muc_dich_vus', function (Blueprint $table) {
            $table->id();
            $table->string('ten_muc')->comment('tên mục , ví dụ như là theo giờ , theo mét vuông')->unique();
            $table->string('slug_ten_muc')->unique();
            $table->integer('so_tien')->comment('số tiền theo từng mục , ví dụ như là tiền theo giờ là 150k , tiền theo giờ định kì thì là 160k');
            $table->integer('is_active')->comment('0 là off , 1 là onl')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danh_muc_dich_vus');
    }
};
