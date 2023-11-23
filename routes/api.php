<?php

use App\Http\Controllers\PersonasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// PERSONAS
Route::post('agregarPersona', [PersonasController::class, 'store']);
Route::get('mostrarPersona/{id}', [PersonasController::class, 'show']);
Route::put('actualizarPersona/{id}', [PersonasController::class, 'update']);
Route::delete('borrarPersona/{id}', [PersonasController::class, 'destroy']);
Route::get('personasDescendente', [PersonasController::class, 'index']);
Route::get('personasAscendente', [PersonasController::class, 'personasAscendente']);
Route::post('personasRango', [PersonasController::class, 'personasPorRangoDeFechas']);
Route::post('buscadorPersonas', [PersonasController::class, 'buscadorPersonas']);