<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\PersonasController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\RolesController;
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

//ROLES
Route::post('agregarRol', [RolesController::class, 'store']);
Route::get('mostrarRol/{id}', [RolesController::class, 'show']);
Route::put('actualizarRol/{id}', [RolesController::class, 'update']);
Route::delete('borrarRol/{id}', [RolesController::class, 'destroy']);
Route::get('rolesDescendente', [RolesController::class, 'index']);
Route::get('rolesAscendente', [RolesController::class, 'ascendente']);
Route::post('rolesRango', [RolesController::class, 'rangoDeFechas']);
Route::post('buscadorRoles', [RolesController::class, 'buscadorRoles']);

//USUARIOS
Route::post('registrarUsuario', [AuthController::class, 'register']);
Route::get('mostrarUsuario/{id}', [AuthController::class, 'show']);
Route::put('actualizarUsuario/{id}', [AuthController::class, 'update']);
Route::delete('borrarUsuario/{id}', [AuthController::class, 'destroy']);
Route::post('login', [AuthController::class, 'login']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
});
Route::get('usuariosDescendente', [AuthController::class, 'index']);
Route::get('usuariosAscendente', [AuthController::class, 'ascendente']);
Route::post('usuariosRango', [AuthController::class, 'rangoDeFechas']);
Route::post('buscadorUsuarios', [AuthController::class, 'buscadorUsuarios']);

//CATEGORIAS
Route::post('agregarCategoria', [CategoriasController::class, 'store']);
Route::get('mostrarCategoria/{id}', [CategoriasController::class, 'show']);
Route::put('actualizarCategoria/{id}', [CategoriasController::class, 'update']);
Route::delete('borrarCategoria/{id}', [CategoriasController::class, 'destroy']);
Route::get('categoriasDescendente', [CategoriasController::class, 'index']);
Route::get('categoriasAscendente', [CategoriasController::class, 'ascendente']);
Route::post('categoriasRango', [CategoriasController::class, 'rangoDeFechas']);
Route::post('buscadorCategorias', [CategoriasController::class, 'buscadorCategorias']);

//PRODUCTOS
Route::post('agregarProducto', [ProductosController::class, 'store']);
Route::get('mostrarProducto/{id}', [ProductosController::class, 'show']);
Route::put('actualizarProducto/{id}', [ProductosController::class, 'update']);
Route::delete('borrarProducto/{id}', [ProductosController::class, 'destroy']);
Route::get('productosDescendente', [ProductosController::class, 'index']);
Route::get('productosAscendente', [ProductosController::class, 'ascendente']);
Route::post('productosRango', [ProductosController::class, 'rangoDeFechas']);
Route::post('buscadorProductos', [ProductosController::class, 'buscadorProductos']);

//PEDIDOS
Route::post('agregarPedido', [PedidosController::class, 'store']);
Route::get('mostrarPedido/{id}', [PedidosController::class, 'show']);
Route::put('actualizarPedido/{id}', [PedidosController::class, 'update']);
Route::delete('borrarPedido/{id}', [PedidosController::class, 'destroy']);
Route::post('estadoPago/{id}', [PedidosController::class, 'estadoPago']);
Route::post('pdf/{id}', [PedidosController::class, 'pdf']);
Route::get('pedidosDescendente', [PedidosController::class, 'index']);
Route::get('pedidosAscendente', [PedidosController::class, 'ascendente']);
Route::post('pedidosRango', [PedidosController::class, 'rangoDeFechas']);
