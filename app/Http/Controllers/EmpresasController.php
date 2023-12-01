<?php

namespace App\Http\Controllers;

use App\Models\Empresas;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class EmpresasController extends Controller
{

    //AGREGAR
    public function store(Request $request)
    {

        //VALIDACION DE CAMPOS
        $validator = Validator::make($request->all(), [
            'nombreEmpresa' => ['required', 'string', 'max:50', 'unique:empresas'],
            'rubroEmpresa' => ['required', 'string', 'max:50'],
            'celularEmpresa' => ['required', 'string', 'max:15', 'min:8', 'regex:/^[0-9()+-]*$/'],
            'direccionEmpresa' => ['required', 'string', 'max:50'],
            'correoEmpresa' => ['nullable', 'email']
        ], [
            'celularEmpresa.min' => 'El numero de celular no tiene los digitos suficientes.',
            'nombreEmpresa.unique' => 'El nombre de la empresa ya existe.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_FOUND);
        }

        $empresa = new Empresas;
        $empresa->nombreEmpresa = $request->nombreEmpresa;
        $empresa->rubroEmpresa = $request->rubroEmpresa;
        $empresa->celularEmpresa = $request->celularEmpresa;
        $empresa->direccionEmpresa = $request->direccionEmpresa;
        $empresa->correoEmpresa = $request->correoEmpresa;
        $empresa->estadoEmpresa = 1; // Establecer el estado a 1
        $empresa->save();

        return response()->json(['message' => 'Se agregó a ' . $request->nombreEmpresa]);
    }



    //MOSTRAR
    public function show($id)
    {

        try {
            $empresa = Empresas::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró a la empresa para mostrarla.'], Response::HTTP_NOT_FOUND);
        }

        return $empresa;
    }



    //ACTUALIZAR
    public function update(Request $request, $id)
    {

        try {
            $empresa = Empresas::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró a la empresa para actualizarla.'], Response::HTTP_NOT_FOUND);
        }


        //VALIDACION DE CAMPOS
        $validator = Validator::make($request->all(), [
            'nombreEmpresa' => ['required', 'string', 'max:50', Rule::unique('empresas')->ignore($id, 'id_empresa')],
            'rubroEmpresa' => ['required', 'string', 'max:50'],
            'celularEmpresa' => ['required', 'string', 'max:50', 'regex:/^[0-9()+-]*$/'],
            'direccionEmpresa' => ['required', 'string', 'max:50'],
            'correoEmpresa' => ['nullable', 'email']
        ], [
            'nombreEmpresa.unique' => 'El nombre de la empresa ya existe.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_FOUND);
        }

        $empresa->nombreEmpresa = $request->nombreEmpresa;
        $empresa->rubroEmpresa = $request->rubroEmpresa;
        $empresa->celularEmpresa = $request->celularEmpresa;
        $empresa->direccionEmpresa = $request->direccionEmpresa;
        $empresa->correoEmpresa = $request->correoEmpresa;
        $empresa->save();

        return response()->json(['message' => 'La empresa ' . $request->nombreEmpresa .  ' ha sido actualizada exitosamente.']);
    }



    //BORRAR
    public function destroy($id)
    {
        try {
            $empresa = Empresas::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró a la empresa para eliminarla.'], Response::HTTP_NOT_FOUND);
        }

        $empresa->delete();
        return response()->json(['message' => 'La empresa' . $empresa->nombreEmpresa . ' ha sido borrada exitosamente.']);
    }

    // MOSTRAR EMPRESAS DESCENDENTE
    public function index()
    {
        return Empresas::orderBy('updated_at', 'desc')->get();
    }

    //MOSTRAR EMPRESAS ASCENDENTE
    public function ascendente()
    {
        return Empresas::orderBy('updated_at', 'asc')->get();
    }

    // MOSTRAR EMPRESAS EN UN RANGO
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

        $personasEnRango = Empresas::whereBetween('updated_at', [$fechaInicio, $fechaFin])->get();

        return response()->json(['empresas' => $personasEnRango]);
    }

    // BUSCADOR DE EMPRESAS
    public function buscadorEmpresas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombreEmpresa' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $nombreEmpresa = $request->nombreEmpresa;

        // Buscar empresas que coincidan con el nombre proporcionado
        $empresas = Empresas::where('nombreEmpresa', 'LIKE', '%' . $nombreEmpresa . '%')->get();

        return response()->json(['empresas' => $empresas]);
    }
}
