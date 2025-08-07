<?php

use App\Http\Controllers\MessengerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/webhook', [MessengerController::class, 'handleWebhook']);
//Route::get('/webhook', [MessengerController::class, 'verifyWebhook']);
