<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class RolesController extends Controller
{

    //AGREGAR
    public function store(Request $request)
    {

        //VALIDACION DE CAMPOS
        $validator = Validator::make($request->all(), [
            'nombreRol' => ['required', 'string', 'max:50', 'unique:roles'],
        ], [
            'nombreRol.unique' => 'El nombre del rol ya existe.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_FOUND);
        }

        $rol = new Roles;
        $rol->nombreRol = $request->nombreRol;
        $rol->estadoRol = 1; // Establecer el estado a 1
        $rol->save();

        return response()->json(['message' => 'Se agregó el rol ' . $request->nombreRol]);
    }



    //MOSTRAR
    public function show($id)
    {

        try {
            $rol = Roles::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró el rol para mostrarlo.'], Response::HTTP_NOT_FOUND);
        }

        return $rol;
    }



    //ACTUALIZAR
    public function update(Request $request, $id)
    {

        try {
            $rol = Roles::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró el rol para actualizarlo.'], Response::HTTP_NOT_FOUND);
        }


        //VALIDACION DE CAMPOS
        $validator = Validator::make($request->all(), [
            'nombreRol' => ['required', 'string', 'max:50', Rule::unique('Roles')->ignore($id, 'id_rol')],
        ], [
            'nombreRol.unique' => 'El nombre del rol ya existe.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_FOUND);
        }

        $rol->nombreRol = $request->nombreRol;
        $rol->save();

        return response()->json(['message' => 'El rol ' . $request->nombreRol .  ' ha sido actualizado exitosamente.']);
    }



    //BORRAR
    public function destroy($id)
    {
        try {
            $rol = Roles::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró el rol para eliminarlo.'], Response::HTTP_NOT_FOUND);
        }

        $rol->delete();
        return response()->json(['message' => 'El rol ' . $rol->nombreRol . ' ha sido borrado exitosamente.']);
    }

    // MOSTRAR ROLES DESCENDENTE
    public function index()
    {
        return Roles::orderBy('updated_at', 'desc')->get();
    }

    //MOSTRAR ROLES ASCENDENTE
    public function ascendente()
    {
        return Roles::orderBy('updated_at', 'asc')->get();
    }

    // MOSTRAR ROLES EN UN RANGO
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

        $rolesEnRango = Roles::whereBetween('updated_at', [$fechaInicio, $fechaFin])->get();

        return response()->json(['Roles' => $rolesEnRango]);
    }

    // BUSCADOR DE ROLES
    public function buscadorRoles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombreRol' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $nombreRol = $request->nombreRol;

        // Buscar Roles que coincidan con el nombre proporcionado
        $Roles = Roles::where('nombreRol', 'LIKE', '%' . $nombreRol . '%')->get();

        return response()->json(['Roles' => $Roles]);
    }
}
