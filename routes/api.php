<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas públicas: login (obtener token).
| Rutas protegidas (auth:sanctum): requieren header
|   Authorization: Bearer {token}
| que se obtiene al hacer login.
|
*/

// --- Rutas públicas ---
Route::post('/login', [AuthController::class, 'login']);

// --- Rutas protegidas (requieren token de Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Endpoint principal: el chatbot llama aquí
    Route::post('/chat/preguntar', [ChatController::class, 'preguntar']);
});
