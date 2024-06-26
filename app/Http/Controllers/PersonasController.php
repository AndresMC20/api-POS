<?php

namespace App\Http\Controllers;

use App\Models\Personas;
use App\Models\Empresas;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class PersonasController extends Controller
{

    //AGREGAR
    public function store(Request $request)
    {

        //VALIDACION DE CAMPOS
        $validator = Validator::make($request->all(), [
            'nombrePersona' => ['required', 'string', 'max:50', 'regex:/^[^\d]+$/'],
            'pApellidoPersona' => ['required', 'string', 'max:50', 'regex:/^[^\d]+$/'],
            'sApellidoPersona' => ['nullable', 'string', 'max:50', 'regex:/^[^\d]+$/'],
            'celularPersona' => ['required', 'string', 'max:15', 'min:8', 'unique:personas', 'regex:/^[0-9()+-]*$/'],
            'direccionPersona' => ['nullable', 'string', 'max:50'],
            'nombreEmpresa' => ['required', 'string', 'max:50'],
        ], [
            'nombrePersona.regex' => 'El nombre no puede contener números.',
            'pApellidoPersona.regex' => 'El apellido no puede contener números.',
            'sApellidoPersona.regex' => 'El apellido no puede contener números.',
            'celularPersona.min' => 'El numero de celular no tiene los digitos suficientes.',
            'celularPersona.unique' => 'El número de celular ya está en uso.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_FOUND);
        }

        // Verificar si la persona ya existe en la base de datos
        $existingPerson = Personas::where([
            'nombrePersona' => $request->nombrePersona,
            'pApellidoPersona' => $request->pApellidoPersona,
            'sApellidoPersona' => $request->sApellidoPersona
        ])->first();

        if ($existingPerson) {
            return response()->json(['error' => 'La persona ya existe en la base de datos.'], Response::HTTP_CONFLICT);
        }

        // Extraer el nombre de la empresa de la entrada
        $nombreEmpresa = $request->nombreEmpresa;

        // Verificar si la empresa existe en la base de datos
        $existingEmpresa = Empresas::where(['nombreEmpresa' => $nombreEmpresa])->first();

        if (!$existingEmpresa) {
            return response()->json(['error' => 'La empresa no existe en la base de datos.'], Response::HTTP_NOT_FOUND);
        }

        $persona = new Personas;
        $persona->nombrePersona       = $request->nombrePersona;
        $persona->pApellidoPersona    = $request->pApellidoPersona;
        $persona->sApellidoPersona    = $request->sApellidoPersona;
        $persona->celularPersona      = $request->celularPersona;
        $persona->direccionPersona    = $request->direccionPersona;
        $persona->estadoPersona = 1; // Establecer el estado a 1
        $persona->save();

        $persona->empresas()->attach($existingEmpresa->id_empresa);

        $message = 'Se agregó a ' . $request->nombrePersona . ' ' . $request->pApellidoPersona;

        if ($request->sApellidoPersona !== null) {
            $message .= ' ' . $request->sApellidoPersona;
        }

        return response()->json(['message' => $message]);
    }



    //MOSTRAR
    public function show($id)
    {

        try {
            $persona = Personas::with('Empresas')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró a la persona para mostrarla.'], Response::HTTP_NOT_FOUND);
        }

        return $persona;
    }



    //ACTUALIZAR
    public function update(Request $request, $id)
    {

        try {
            $persona = Personas::with('Empresas')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró a la persona para actualizarla.'], Response::HTTP_NOT_FOUND);
        }

        //VALIDACION DE CAMPOS
        $validator = Validator::make($request->all(), [
            'nombrePersona' => ['required', 'string', 'max:50', 'regex:/^[^\d]+$/'],
            'pApellidoPersona' => ['required', 'string', 'max:50', 'regex:/^[^\d]+$/'],
            'sApellidoPersona' => ['nullable', 'string', 'max:50', 'regex:/^[^\d]+$/'],
            'celularPersona' => ['required', 'string', 'max:15', 'min:8', 'regex:/^[0-9()+-]*$/', Rule::unique('personas')->ignore($id, 'id_persona')],
            'direccionPersona' => ['nullable', 'string', 'max:50'],
            'nombreEmpresa' => ['required', 'array']
        ], [
            'nombrePersona.regex' => 'El nombre no puede contener números.',
            'pApellidoPersona.regex' => 'El apellido no puede contener números.',
            'sApellidoPersona.regex' => 'El apellido no puede contener números.',
            'celularPersona.min' => 'El numero de celular no tiene los digitos suficientes.',
            'celularPersona.unique' => 'El número de celular ya está en uso.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_FOUND);
        }

        // Verificar si la persona ya existe en la base de datos
        $existingPerson = Personas::where([
            'nombrePersona' => $request->nombrePersona,
            'pApellidoPersona' => $request->pApellidoPersona,
            'sApellidoPersona' => $request->sApellidoPersona
        ])->where('id_persona', '<>', $persona->id_persona)->first();

        if ($existingPerson) {
            return response()->json(['error' => 'La persona ya existe en la base de datos.'], Response::HTTP_CONFLICT);
        }

        // Obtener los IDs de las empresas a partir de los nombres
        $empresaNombres = $request->nombreEmpresa;
        $empresaIds = Empresas::whereIn('nombreEmpresa', $empresaNombres)->pluck('id_empresa')->toArray();

        if (!$empresaIds) {
            return response()->json(['error' => 'La empresa no existe en la base de datos.'], Response::HTTP_NOT_FOUND);
        }

        $persona->nombrePersona       = $request->nombrePersona;
        $persona->pApellidoPersona    = $request->pApellidoPersona;
        $persona->sApellidoPersona    = $request->sApellidoPersona;
        $persona->celularPersona      = $request->celularPersona;
        $persona->direccionPersona    = $request->direccionPersona;
        $persona->save();

        // Sincronizar las empresas asignadas a la persona
        $persona->empresas()->sync($empresaIds);

        $message = 'Se actualizó a ' . $request->nombrePersona . ' ' . $request->pApellidoPersona;

        if ($request->sApellidoPersona !== null) {
            $message .= ' ' . $request->sApellidoPersona;
        }

        return response()->json(['message' => $message]);
    }



    //BORRAR
    public function destroy($id)
    {
        try {
            $persona = Personas::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró a la persona para eliminarla.'], Response::HTTP_NOT_FOUND);
        }

        $nombreCompleto = $persona->nombrePersona . ' ' . $persona->pApellidoPersona;

        if ($persona->sApellidoPersona !== null) {
            $nombreCompleto .= ' ' . $persona->sApellidoPersona;
        }

        $persona->delete();
        return response()->json(['message' => 'Se ha eliminado a ' . $nombreCompleto . ' exitosamente.']);
    }

    // MOSTRAR PERSONAS DESCENDENTE
    public function index()
    {
        return Personas::with('empresas')->orderBy('updated_at', 'desc')->get();
    }

    //MOSTRAR PERSONAS ASCENDENTE
    public function ascendente()
    {
        return Personas::with('empresas')->orderBy('updated_at', 'asc')->get();
    }

    // MOSTRAR PERSONAS EN UN RANGO
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

        $personasEnRango = Personas::with('empresas')->whereBetween('updated_at', [$fechaInicio, $fechaFin])->get();

        return response()->json(['personas' => $personasEnRango]);
    }

    // BUSCADOR DE PERSONAS
    public function buscadorPersonas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombrePersona' => ['nullable', 'string'],
            'pApellidoPersona' => ['nullable', 'string'],
            'sApellidoPersona' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $nombrePersona = $request->nombrePersona;
        $pApellidoPersona = $request->pApellidoPersona;
        $sApellidoPersona = $request->sApellidoPersona;

        $query = Personas::with('empresas')->where('nombrePersona', 'LIKE', '%' . $nombrePersona . '%')
            ->where('pApellidoPersona', 'LIKE', '%' . $pApellidoPersona . '%');

        if ($sApellidoPersona) {
            $query->where('sApellidoPersona', 'LIKE', '%' . $sApellidoPersona . '%');
        }

        $result = $query->get();

        return response()->json(['personas' => $result]);
    }
}
