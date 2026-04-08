<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\FixtureController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\UserController;

// IA
use App\Http\Controllers\Ia\AiPredictionController;
use App\Http\Controllers\Ia\IaSubscriptionController;
use App\Http\Controllers\Ia\PredictionController;

// Pots & Subscriptions
use App\Http\Controllers\PotController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\PotAdminController;

/*
|--------------------------------------------------------------------------
| 🔓 ROUTES PUBLIQUES
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/login', [SecurityController::class, 'login']);
Route::post('/register', [SecurityController::class, 'register']);

// Pots
Route::get('/pots', [PotController::class, 'index']);
Route::get('/pots/{pot}', [PotController::class, 'show']);
Route::get('/pots/{pot}/details', [PotController::class, 'details']);

// Fixtures
Route::get('/fixtures', [FixtureController::class, 'index']);
Route::get('/all-fixtures', [FixtureController::class, 'fixtures']);

// Paiement
Route::get('/pay/status/{referenceId}', [MobileController::class, 'checkStatus']);

// IA publique
Route::get('/predictions', [AiPredictionController::class, 'index']);
Route::get('/predictions/{id}', [AiPredictionController::class, 'show'])
    ->whereNumber('id');


/*
|--------------------------------------------------------------------------
| 🔐 ROUTES AUTHENTIFIÉES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [SecurityController::class, 'logout']);
    Route::post('/profile', [SecurityController::class, 'updateProfile']);

    // Paiement
    Route::post('/pay/deposit', [MobileController::class, 'deposit']);
    Route::post('/pay/withdraw', [MobileController::class, 'withdraw']);
    Route::post('/recharges', [AccountController::class, 'recharge']);

    // Compte
    Route::get('/me', [AccountController::class, 'me']);
    Route::get('/dashboard/stats', [AccountController::class, 'index']);
    Route::get('/account/transactions', [AccountController::class, 'transactions']);
    Route::get('/account/pronostics', [AccountController::class, 'pronostics']);
    Route::get('/account/pots', [AccountController::class, 'pots']);

    // Pots
    Route::post('/pots', [PotController::class, 'createPotFoot']);
    Route::post('/pots/{pot}/join', [SubscriptionController::class, 'joinPot']);
    Route::get('/pots/{pot}/leaderboard', [PotController::class, 'leaderboard']);

    // Prédictions
    Route::post('/pots/{pot}/lines/{line}/predict', [PredictionController::class, 'store']);
    Route::post('/unlock-matches', [PredictionController::class, 'unlock']);
    Route::post('/unlock-admin-matches', [PredictionController::class, 'unlockAdmin']);

    // IA
    Route::get('/predictions/history', [AiPredictionController::class, 'history']);
    Route::post('/predictions/unlock', [AiPredictionController::class, 'unlock']);

    // Abonnements IA
    Route::get('/ia_subscriptions', [IaSubscriptionController::class, 'index']);
    Route::post('/ia_subscriptions', [IaSubscriptionController::class, 'store']);
});


/*
|--------------------------------------------------------------------------
| 🛠️ ROUTES ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'can:admin'])->group(function () {

    Route::post('/admin/pots', [PotAdminController::class, 'store']);
    Route::patch('/admin/pots/{pot}/settle', [PotAdminController::class, 'settle']);

});
