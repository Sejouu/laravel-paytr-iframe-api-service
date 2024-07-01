<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CartController;


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
Route::any('/sepet/odeme', [CartController::class, 'pay'])->name('cart.pay');
Route::post('/paytr/notification', [CartController::class, 'handlePayTRNotification'])->name('cart.paytr.notification');

Route::get('/odeme/basarili', [CartController::class, 'success'])->name('cart.pay.success');
Route::get('/odeme/hata', [CartController::class, 'fail'])->name('cart.pay.fail');


