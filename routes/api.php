<?php

use App\Http\Controllers\Api\V1\PostController as V1PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/posts', [V1PostController::class, 'index']);
    Route::get('/posts/{id}', [V1PostController::class, 'show']);
});
