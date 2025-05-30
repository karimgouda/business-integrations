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
Route::get('/slack/get-messages', [SlackController::class, 'getMessages']);
Route::get('send-message-view',[DebugController::class,'slackView']);

Route::get('/slack/get-mention-users', [SlackController::class,'getUsers']);



Route::get('send-users',[DebugController::class,'getSlackUsers']);
Route::post('debug',[DebugController::class,'index']);
