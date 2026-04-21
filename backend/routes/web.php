<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root ke dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ============================================================
// DASHBOARD ROUTES
// ============================================================
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/realtime', [DashboardController::class, 'realtimeData'])->name('dashboard.realtime');

// ============================================================
// AUTH ROUTES
// ============================================================
Route::get('/login', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    
    if (request()->expectsJson()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated. Please login first.'
        ], 401);
    }
    
    $frontendUrl = env('FRONTEND_URL', 'http://localhost:5500');
    return redirect()->away($frontendUrl . '/index.html');
})->name('login');

Route::post('/logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    
    $frontendUrl = env('FRONTEND_URL', 'http://localhost:5500');
    return redirect()->away($frontendUrl . '/index.html');
})->name('logout');

Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json([
            'success' => false,
            'message' => 'Route not found.'
        ], 404);
    }
    
    return response()->view('errors.404', [], 404);
});