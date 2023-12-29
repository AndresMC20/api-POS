<?php

namespace App\Http\Controllers;

use App\Models\Productos;
use App\Models\Categorias;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class ProductosController extends Controller
{

    //AGREGAR
    public function store(Request $request)
    {
        //VALIDACION DE CAMPOS
        $validator = Validator::make($request->all(), [
            'nombreCategoria' => ['required', 'string', 'max:50', 'exists:categorias,nombreCategoria'],
            'nombreProducto' => ['required', 'string', 'max:50']
        ], [
            'nombreCategoria.exists' => 'El nombre de la categoria no existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_FOUND);
        }

        // Buscar la categoria por nombre
        $categoria = Categorias::where('nombreCategoria', $request->nombreCategoria)->first();

        // Verificar si el producto con el mismo nombre y categoría ya existe
        $existingProduct = Productos::where('nombreProducto', $request->nombreProducto)
            ->where('id_categoria', $categoria->id_categoria) // Utilizar el ID de la categoría encontrada
            ->first();

        if ($existingProduct) {
            return response()->json(['error' => 'El producto ya existe en la categoria.'], Response::HTTP_BAD_REQUEST);
        }


        $producto = new Productos;
        $producto->nombreProducto = $request->nombreProducto;
        $producto->estadoProducto = 1; // Establecer el estado a 1
        $producto->id_categoria = $categoria->id_categoria; // Utilizar el ID de la categoria encontrada
        $producto->save();

        return response()->json(['message' => 'Se agregó el producto ' . $request->nombreProducto . ' en la categoria ' . $request->nombreCategoria]);
    }


    //MOSTRAR
    public function show($id)
    {
        try {
            $producto = Productos::with('categoria')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró el producto para mostrarlo.'], Response::HTTP_NOT_FOUND);
        }

        return $producto;
    }


    //ACTUALIZAR
    public function update(Request $request, $id)
    {
        try {
            $producto = Productos::with('categoria')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró el producto para actualizarlo.'], Response::HTTP_NOT_FOUND);
        }

        //VALIDACION DE CAMPOS
        $validator = Validator::make($request->all(), [
            'nombreCategoria' => ['required', 'string', 'max:50', 'exists:categorias,nombreCategoria'],
            'nombreProducto' => ['required', 'string', 'max:50']
        ], [
            'nombreCategoria.exists' => 'El nombre de la categoria no existe.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_FOUND);
        }

        // Buscar la categoria por nombre
        $categoria = Categorias::where('nombreCategoria', $request->nombreCategoria)->first();

        // Verificar si el producto con el mismo nombre y categoría ya existe
        $existingProduct = Productos::where('nombreProducto', $request->nombreProducto)
            ->where('id_categoria', $categoria->id_categoria) // Utilizar el ID de la categoría encontrada
            ->first();

        if ($existingProduct) {
            return response()->json(['error' => 'El producto ya existe la categoria.'], Response::HTTP_BAD_REQUEST);
        }


        $producto->nombreProducto = $request->nombreProducto;
        $producto->id_categoria = $categoria->id_categoria; // Utilizar el ID de la categoria encontrada
        $producto->save();

        return response()->json(['message' => 'Se actualizó a ' . $request->nombreCategoria . ' ' . $request->nombreProducto . ' exitosamente.']);
    }


    //BORRAR
    public function destroy($id)
    {
        try {
            $producto = Productos::with('categoria')->findOrFail($id);
            $nombreCategoria = $producto->categoria->nombreCategoria; 
            $nombreProducto = $producto->nombreProducto;
            $producto->delete();

            return response()->json([
                'message' => 'El producto ' . $nombreCategoria . ' ' . $nombreProducto . ' ha sido borrado exitosamente.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró el producto para eliminarlo.'], Response::HTTP_NOT_FOUND);
        }
    }


    // MOSTRAR PRODUCTOS DESCENDENTE
    public function index()
    {
        return Productos::with('categoria')->orderBy('updated_at', 'desc')->get();
    }


    //MOSTRAR PRODUCTOS ASCENDENTE
    public function ascendente()
    {
        return Productos::with('categoria')->orderBy('updated_at', 'asc')->get();
    }


    // MOSTRAR PRODUCTOS EN UN RANGO
    public function rangoDeFechas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $fechaInicio = $request->fechaInicio;
        $fechaFin = $request->fechaFin;

        $productosEnRango = Productos::with('categoria')->whereBetween('updated_at', [$fechaInicio, $fechaFin])->get();

        return response()->json(['productos' => $productosEnRango]);
    }


    // BUSCADOR DE PRODUCTOS
    public function buscadorProductos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombreProducto' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $nombreProducto = $request->nombreProducto;

        // Buscar productos que coincidan con el nombre proporcionado
        $Productos = Productos::with('categoria')->where('nombreProducto', 'LIKE', '%' . $nombreProducto . '%')->get();

        return response()->json(['Productos' => $Productos]);
    }
    
}
