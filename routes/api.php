<?php

use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ParticipationController;


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', function (Request $request) {
    return UserController::login($request);
});

// Public routes for guest access
Route::get('contests', [ContestController::class, 'index']);
Route::get('contests/{contest}', [ContestController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });
    // Contest routes
    // Protected contest routes (create, update, delete)
    Route::apiResource('contests', ContestController::class)->except(['index', 'show']);

    Route::apiResource('contests.questions', QuestionController::class);
    // Participation routes
    Route::apiResource('participations', ParticipationController::class)->except(['destroy']);

    // Leaderboard routes
    Route::get('contests/{contest}/leaderboard', [LeaderboardController::class, 'show']);

    // User history routes
    Route::get('user/prizes', [UserController::class, 'prizes']);
    Route::get('user/participation-history', [UserController::class, 'participationHistory']);
    Route::get('user/in-progress-contests', [UserController::class, 'inProgressContests']);
    Route::get('user/dashboard', [UserController::class, 'dashboard']);
});
