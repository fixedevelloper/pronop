<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;


Route::post('/momo/callback', [MomoCallbackController::class, 'callback'])->name('momo.callback');

Route::get('/payment/mock/{ref}', [FrontController::class, 'mockPayment'])->name('payment.mock');
Route::post('/payment/confirm/{ref}', [FrontController::class, 'confirmPayment'])->name('payment.confirm');
Route::get('/pay/{reference}', [PaymentController::class, 'show'])->name('paiement.show');
Route::get('/payment/success/{order}', [PaymentController::class, 'success']);
Route::get('/payment/cancel/{order}', [PaymentController::class, 'cancel']);
