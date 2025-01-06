<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuaChonDichVu extends Model
{
    use HasFactory;
    protected $table = 'lua_chon_dich_vus';
    protected $fillable = [
        'ten_lua_chon',
        'slug_dich_vu',
        'icon_dich_vu',
        'id_muc',
        'is_active',
    ];
}
