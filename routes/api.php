<?php


use App\Http\Controllers\AccountController;
use App\Http\Controllers\FixtureController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\SecurityController;

use App\Http\Controllers\UserController;
use App\Http\Resources\FixtureResource;
use Illuminate\Support\Facades\Route;


// 🔹 Routes publiques
Route::post('/login', [SecurityController::class, 'login']);
Route::post('/register', [SecurityController::class, 'register']);
Route::get('/pots', [App\Http\Controllers\PotController::class, 'index']);
Route::get('/pots/{pot}', [App\Http\Controllers\PotController::class, 'show']);
Route::get('/fixtures', [FixtureController::class, 'index']);
Route::get('/pots/{pot}/details', [App\Http\Controllers\PotController::class, 'details']);
Route::get('/pay/status/{referenceId}', [MobileController::class, 'checkStatus']);
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [SecurityController::class, 'logout']);
    Route::post('/pay/deposit', [MobileController::class, 'deposit']);

    Route::post('/pay/withdraw', [MobileController::class, 'withdraw']);
    Route::get('/account/transactions', [AccountController::class, 'transactions']);
    Route::get('/account/pronostics', [AccountController::class, 'pronostics']);
    Route::get('/account/pots', [AccountController::class, 'pots']);
    Route::post('/pots/{pot}/join', [App\Http\Controllers\SubscriptionController::class, 'joinPot']);
    Route::get('/pots/{pot}/leaderboard', [App\Http\Controllers\PotController::class, 'leaderboard']);
    Route::post('/pots', [App\Http\Controllers\PotController::class, 'createPotFoot']);
    Route::post('/pots/{pot}/lines/{line}/predict', [App\Http\Controllers\PredictionController::class, 'store']);
});

// Admin routes (protéger avec middleware admin)
Route::middleware(['auth:sanctum', 'can:admin'])->group(function () {
    Route::post('/admin/pots', [App\Http\Controllers\Admin\PotAdminController::class, 'store']);
    Route::patch('/admin/pots/{pot}/settle', [App\Http\Controllers\Admin\PotAdminController::class, 'settle']);
});






