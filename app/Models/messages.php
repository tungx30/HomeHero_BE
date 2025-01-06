<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class messages extends Model
{
    use HasFactory;
    protected $table ='messages';
    protected $fillable = [
        'nguoi_gui_id',
        'nguoi_nhan_id',
        'noi_dung',
        'sender_type',
    ];
}
