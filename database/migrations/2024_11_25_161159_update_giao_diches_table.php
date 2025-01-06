<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('giao_diches', function (Blueprint $table) {
            $table->integer('debitAmount')->nullable()->comment('Số tiền đã chuyển');
            $table->integer('type')->comment('1 là nạp tiền vào, 2 là rút tiền, 3 là thanh toán');
            $table->integer('is_duyet')->default(0)->comment('0: chưa duyệt, 1: đã duyệt');
            $table->integer('is_done')->default(0)->comment('0: chưa hoàn thành, 1: đã hoàn thành');
            $table->foreignId('id_don_hang')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Schema::table('giao_diches', function (Blueprint $table) {
        //     $table->dropColumn(['debitAmount', 'type', 'is_duyet', 'is_done']);
        //     $table->foreignId('id_don_hang')->nullable(false)->change();
        // });
    }
};

