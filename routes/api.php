<?php

use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\PersonasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// PERSONAS
Route::post('agregarPersona', [PersonasController::class, 'store']);
Route::get('mostrarPersona/{id}', [PersonasController::class, 'show']);
Route::put('actualizarPersona/{id}', [PersonasController::class, 'update']);
Route::delete('borrarPersona/{id}', [PersonasController::class, 'destroy']);
Route::get('personasDescendente', [PersonasController::class, 'index']);
Route::get('personasAscendente', [PersonasController::class, 'ascendente']);
Route::post('personasRango', [PersonasController::class, 'rangoDeFechas']);
Route::post('buscadorPersonas', [PersonasController::class, 'buscadorPersonas']);

// EMPRESAS
Route::post('agregarEmpresa', [EmpresasController::class, 'store']);
Route::get('mostrarEmpresa/{id}', [EmpresasController::class, 'show']);
Route::put('actualizarEmpresa/{id}', [EmpresasController::class, 'update']);
Route::delete('borrarEmpresa/{id}', [EmpresasController::class, 'destroy']);
Route::get('empresasDescendente', [EmpresasController::class, 'index']);
Route::get('empresasAscendente', [EmpresasController::class, 'ascendente']);
Route::post('empresasRango', [EmpresasController::class, 'rangoDeFechas']);
Route::post('buscadorEmpresas', [EmpresasController::class, 'buscadorEmpresas']);