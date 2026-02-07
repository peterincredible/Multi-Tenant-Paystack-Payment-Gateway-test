<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\PayStackController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/application', [ApplicationController::class, 'store']);
Route::post('/initializePayment', [PayStackController::class, 'initializePayment']);
Route::get('/redirect-callback', [PayStackController::class, 'callbackURL'])->name('callback_url');
Route::post('/webhook-listener', [PayStackController::class, 'webhookURL'])->name('webhook_url');
//449bf648-f264-496a-9f58-938600a20021
//https://mighty-plums-lick.loca.lt/api/webhook-listener

