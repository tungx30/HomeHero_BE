<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomersController;

Route::get('/', function () {
    try {
        // Code của bạn ở đây
        return response()->json(['success' => 2]);
    } catch (\Exception $e) {
        // Trả về lỗi dưới dạng JSON
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
});


