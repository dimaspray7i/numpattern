<?php
// routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| All routes are prefixed with /api automatically by Laravel.
| Rate limiting: 'api' throttle (60/min) applied globally via kernel.
*/

// ── Public: Auth ──────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register'])
     ->middleware('throttle:10,1'); // max 10 registrations per minute per IP

Route::post('/login', [AuthController::class, 'login'])
     ->middleware('throttle:10,1');

// ── Protected: Auth ───────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // ── Game ──────────────────────────────────────────────────────────────
    Route::post('/start-session',    [GameController::class, 'startSession']);
    Route::get('/generate-question', [GameController::class, 'generateQuestion']);
    Route::post('/submit-answer',    [GameController::class, 'submitAnswer']);
    Route::post('/end-game',         [GameController::class, 'endGame']);
    Route::get('/get-score',         [GameController::class, 'getScore']);

    // ── Leaderboard & Stats ───────────────────────────────────────────────
    Route::get('/leaderboard',       [GameController::class, 'leaderboard']);
    Route::get('/stats',             [GameController::class, 'stats']);
});
