<?php

use App\Http\Controllers\FrontController;
use Illuminate\Support\Facades\Route;


Route::post('/momo/callback', [MomoCallbackController::class, 'callback'])->name('momo.callback');

Route::get('/payment/mock/{ref}', [FrontController::class, 'mockPayment'])->name('payment.mock');
Route::post('/payment/confirm/{ref}', [FrontController::class, 'confirmPayment'])->name('payment.confirm');
