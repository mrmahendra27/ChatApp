<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/chats', [App\Http\Controllers\ChatController::class, 'index'])->name('chats');

Route::get('/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('messages');

Route::post('/send-message', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('send-message');