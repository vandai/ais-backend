<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EventCategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FootballController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes
// Route::post('/register', [AuthController::class, 'register']); // Registration disabled
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// News endpoints (public)
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/search', [NewsController::class, 'search']);
Route::get('/news/{news_id}', [NewsController::class, 'show']);

// Categories endpoints (public)
Route::get('/categories', [CategoryController::class, 'index']);

// Events endpoints (public)
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event_id}', [EventController::class, 'show']);
Route::get('/event-categories', [EventCategoryController::class, 'index']);

// Profile endpoints (public)
Route::get('/profile/member/{member_number}', [ProfileController::class, 'getByMemberNumber']);

// Football endpoints (public)
Route::prefix('football')->group(function () {
    Route::get('/seasons', [FootballController::class, 'seasons']);
    Route::get('/competitions', [FootballController::class, 'competitions']);
    Route::get('/fixtures', [FootballController::class, 'fixtures']);
    Route::get('/fixtures/next', [FootballController::class, 'nextFixture']);
    Route::get('/results', [FootballController::class, 'results']);
    Route::get('/results/last', [FootballController::class, 'lastResult']);
    Route::get('/results/{fixture_id}/report', [FootballController::class, 'matchReport']);
    Route::get('/standings', [FootballController::class, 'standings']);
    Route::get('/standings/all', [FootballController::class, 'allStandings']);
    Route::get('/arsenal/stats', [FootballController::class, 'arsenalStats']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/password', [AuthController::class, 'updatePassword']);
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
        ->middleware('throttle:6,1');
    Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('api.verification.verify');

    // Profile endpoints
    Route::get('/profile/user/{user_id}', [ProfileController::class, 'getByUserId']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile/picture', [ProfileController::class, 'deleteProfilePicture']);
});
