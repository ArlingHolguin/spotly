<?php
//api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UrlController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->prefix('v1')->group(function () {
    Route::get('/urls', [UrlController::class, 'index'])->name('api.urls.index'); 
    Route::middleware(['throttle:20,1'])->group(function () {
        Route::post('/urls', [UrlController::class, 'store'])->name('api.urls.store');
    });

    Route::delete('/urls/{short_code}', [UrlController::class, 'destroy'])->name('api.urls.destroy');   


    // Rutas protegidas (requieren autenticación)
    Route::middleware('auth:sanctum')->group(function () {
        Route::put('/urls/{short_code}', [UrlController::class, 'update'])->name('api.urls.update'); 
    });


    
});

Route::middleware(['api'])->prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register'); 
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login'); 
});

Route::get('/test', function () {        
    return response()->json(['message' => 'Hello World']);
});



