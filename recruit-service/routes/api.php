<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\RecruitmentPostController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\InterviewSlotController;
use App\Http\Controllers\InterviewResultController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/clubs', [ClubController::class, 'index']);
    Route::get('/clubs/{club}', [ClubController::class, 'show']);

    Route::get('/recruitment-posts', [RecruitmentPostController::class, 'index']);
    Route::get('/recruitment-posts/{post}', [RecruitmentPostController::class, 'show']);

    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::post('/applications', [ApplicationController::class, 'store']);
    Route::get('/applications/{application}', [ApplicationController::class, 'show']);

    Route::get('/interview-slots', [InterviewSlotController::class, 'index']);
    Route::post('/interview-slots/{slot}/select', [InterviewSlotController::class, 'selectSlot']);

    Route::get('/interview-results', [InterviewResultController::class, 'index']);

    Route::middleware('role:club_leader')->group(function () {
        Route::post('/clubs', [ClubController::class, 'store']);
        Route::put('/clubs/{club}', [ClubController::class, 'update']);

        Route::post('/recruitment-posts', [RecruitmentPostController::class, 'store']);
        Route::put('/recruitment-posts/{post}', [RecruitmentPostController::class, 'update']);

        Route::get('/club/applications', [ApplicationController::class, 'clubApplications']);
        Route::put('/applications/{application}/status', [ApplicationController::class, 'updateStatus']);

        Route::post('/interview-slots', [InterviewSlotController::class, 'store']);
        Route::put('/interview-slots/{slot}', [InterviewSlotController::class, 'update']);
        Route::delete('/interview-slots/{slot}', [InterviewSlotController::class, 'destroy']);
        Route::get('/club/interview-slots', [InterviewSlotController::class, 'clubSlots']);

        Route::post('/interview-results', [InterviewResultController::class, 'store']);
        Route::put('/interview-results/{result}', [InterviewResultController::class, 'update']);
    });
});
