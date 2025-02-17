<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilmsController;
use App\Http\Controllers\PlanetsController;
use App\Http\Middleware\ValidateSanctumToken;
use App\Http\Controllers\CharactersController;

Route::post('/user', [AuthController::class, 'createUser']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(ValidateSanctumToken::class)->group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/characters/{id}', [CharactersController::class, 'show']);
    Route::get('/planets/{id}', [PlanetsController::class, 'show']);
    Route::get('/films/{id}', [FilmsController::class, 'show']);
    Route::get('/visits', [AuthController::class, 'showHistory']);
});