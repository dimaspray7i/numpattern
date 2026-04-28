<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // User logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Game endpoints
    Route::post('/start-session', [GameController::class, 'startSession']);
    Route::get('/generate-question', [GameController::class, 'generateQuestion']);
    Route::post('/submit-answer', [GameController::class, 'submitAnswer']);
    Route::post('/end-game', [GameController::class, 'endGame']);
    
    // Score & Stats endpoints
    Route::get('/get-score', [GameController::class, 'getScore']);
    Route::get('/leaderboard', [GameController::class, 'leaderboard']);
    Route::get('/stats', [GameController::class, 'stats']);
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/data', [DashboardController::class, 'realtimeData']);
});

// Fallback untuk API
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
    ], 404);
});