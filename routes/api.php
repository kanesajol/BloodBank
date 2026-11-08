<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BloodCollectionController;
use App\Http\Controllers\Api\ConsumptionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StaffController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/signup', [AuthController::class, 'signup']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::patch('/profile', [AuthController::class, 'updateProfile']);
    Route::patch('/profile/password', [AuthController::class, 'changePassword']);

    Route::get('/donors/search', [BloodCollectionController::class, 'search']);
    Route::get('/donors/search-by-location', [BloodCollectionController::class, 'searchByLocation']);
    Route::get('/donors/returning', [BloodCollectionController::class, 'returning']);
    Route::delete('/donors', [BloodCollectionController::class, 'destroyAll']);
    Route::get('/donors/meta', [BloodCollectionController::class, 'meta']);
    Route::post('/donors', [BloodCollectionController::class, 'storeDonor']);
    Route::get('/donors/{donor}', [BloodCollectionController::class, 'show']);
    Route::patch('/donors/{donor}', [BloodCollectionController::class, 'updateDonor']);
    Route::delete('/donors/{donor}', [BloodCollectionController::class, 'destroyDonor']);
    Route::post('/donors/{donor}/donations', [BloodCollectionController::class, 'storeDonation']);
    Route::delete('/donations/{donation}', [BloodCollectionController::class, 'destroyDonation']);

    Route::get('/consumption/meta', [ConsumptionController::class, 'meta']);
    Route::get('/consumption/returning-patients', [ConsumptionController::class, 'returningPatients']);
    Route::get('/consumption', [ConsumptionController::class, 'index']);
    Route::post('/consumption', [ConsumptionController::class, 'store']);
    Route::patch('/consumption/{consumption}', [ConsumptionController::class, 'update']);
    Route::delete('/consumption', [ConsumptionController::class, 'destroyAll']);
    Route::delete('/consumption/{consumption}', [ConsumptionController::class, 'destroy']);

    Route::get('/reports', [ReportController::class, 'index']);

    Route::get('/staff/roles', [StaffController::class, 'roles']);
    Route::get('/staff/log', [StaffController::class, 'log']);
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::delete('/staff/{user}', [StaffController::class, 'destroy']);
});
