<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\TempRfidController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Present Route
Route::post('/v1/attendance', [PresensiController::class, 'store']);
Route::get('/v1/daily-attendance', [PresensiController::class, 'getDailyAttandance']);

// Temp Rfid Route
Route::post('/v1/rfids', [TempRfidController::class, 'store']);

// Tool Route
Route::get('/v1/tools/{code}', [ToolController::class, 'getByCode']);
