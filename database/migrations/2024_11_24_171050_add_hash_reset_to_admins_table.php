<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('hash_reset')
                ->nullable()
                ->comment('Dùng để reset mật khẩu qua email')
                ->after('password'); // Thêm sau cột password
        });
    }

    public function down(): void
    {
        // Schema::table('admins', function (Blueprint $table) {
        //     $table->dropColumn('hash_reset');
        // });
    }

};
