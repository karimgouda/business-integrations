<?php

use App\Http\Controllers\Apps\SlackController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;







Route::get('/login',[AuthController::class,'loginPage'])->name('loginPage')->middleware('guest');
Route::post('/sign-in',[AuthController::class,'login'])->name('login');


Route::middleware('auth')->group(function (){
    Route::get('/slack/chat', [SlackController::class,'slackChat'])->name('slackChat');
    Route::get('slack/oauth/callback',[SlackController::class,'callback']);
    Route::post('/slack/send-message', [SlackController::class, 'sendMessage']);
    Route::get('/slack/get-messages', [SlackController::class, 'getMessages']);
    Route::get('/slack/get-mention-users', [SlackController::class,'getUsers']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/',[HomeController::class,'index'])->name('integration');
});




Route::get('send-users',[DebugController::class,'getSlackUsers']);
Route::post('debug',[DebugController::class,'index']);
Route::get('send-message-view',[DebugController::class,'slackView']);
