<?php

use Illuminate\Support\Facades\Route;

// 動作確認用（/api/ping にアクセスすると {"pong": true} が返る）
Route::get('/ping', fn () => ['pong' => true]);
