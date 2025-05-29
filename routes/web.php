<?php

use App\Http\Controllers\Apps\SlackController;
use App\Http\Controllers\DebugController;
use Illuminate\Support\Facades\Route;





Route::get('/',function (){
   return view('app.auth.register');
});

Route::get('/slack/chat', [SlackController::class,'slackChat']);
Route::get('slack/oauth/callback',[SlackController::class,'callback']);
Route::post('/slack/send-message', [SlackController::class, 'sendMessage']);
Route::get('send-message-view',[DebugController::class,'slackView']);
Route::get('send-users',[DebugController::class,'getSlackUsers']);
Route::post('debug',[DebugController::class,'index']);
