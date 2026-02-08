<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NodeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('node')->group(function () {
    Route::post('/ping', [NodeController::class, 'ping']);
    Route::post('/status', [NodeController::class, 'updateStatus']);
    Route::get('/config', [NodeController::class, 'getConfig']);
});
