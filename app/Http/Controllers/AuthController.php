<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Personas;
use App\Models\Roles;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\Rule;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthController extends Controller
{

    // REGISTER
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'nombrePersona' => ['required', 'string', 'max:50'],
            'nombreRol' => ['required', 'string', 'max:50', 'exists:roles,nombreRol']
        ], [
            'email.unique' => 'El email ya existe.',
            'nombreRol.exists' => 'El nombre del rol no existe.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_FOUND);
        }

        // Extraer el nombre de la persona de la entrada
        $nombrePersona = $request->nombrePersona;
        $nombreArray = explode(" ", $nombrePersona);

        $existingPerson = null;

        // Verificar si la persona ya existe en la base de datos
        if (count($nombreArray) >= 2) {
            $nombre = $nombreArray[0];
            $pApellido = $nombreArray[1];

            $existingPerson = Personas::where('nombrePersona', 'LIKE', '%' . $nombre . '%')
                ->where('pApellidoPersona', 'LIKE', '%' . $pApellido . '%')
                ->first();
        }

        if (!$existingPerson) {
            return response()->json(['error' => 'La persona no existe en la base de datos.'], Response::HTTP_NOT_FOUND);
        }


        $rol = Roles::where('nombreRol', $request->nombreRol)->first();

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->id_persona = $existingPerson->id_persona;
        $user->id_rol = $rol->id_rol;
        $user->estadoUsuario = 1; // Establecer el estado a 1
        $user->save();

        return response()->json(['message' => 'Se agregó a ' . $request->name]);
    }


    //MOSTRAR
    public function show($id)
    {

        try {
            $user = User::with('personas', 'rol')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró al usuario para mostrarlo.'], Response::HTTP_NOT_FOUND);
        }

        return $user;
    }


    //ACTUALIZAR
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontro al usuario para actualizarlo']);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users')->ignore($id)],
            'password' => ['required', 'confirmed'],
            'nombrePersona' => ['required', 'string', 'max:50'],
            'nombreRol' => ['required', 'string', 'max:50', 'exists:roles,nombreRol']
        ], [
            'email.unique' => 'El email ya existe.',
            'nombreRol.exists' => 'El nombre del rol no existe.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_FOUND);
        }

        // Extraer el nombre de la persona de la entrada
        $nombrePersona = $request->nombrePersona;
        $nombreArray = explode(" ", $nombrePersona);

        $existingPerson = null;

        // Verificar si la persona ya existe en la base de datos
        if (count($nombreArray) >= 2) {
            $nombre = $nombreArray[0];
            $pApellido = $nombreArray[1];

            $existingPerson = Personas::where('nombrePersona', 'LIKE', '%' . $nombre . '%')
                ->where('pApellidoPersona', 'LIKE', '%' . $pApellido . '%')
                ->first();
        }

        if (!$existingPerson) {
            return response()->json(['error' => 'La persona no existe en la base de datos.'], Response::HTTP_NOT_FOUND);
        }

        $rol = Roles::where('nombreRol', $request->nombreRol)->first();

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->id_persona = $existingPerson->id_persona;
        $user->id_rol = $rol->id_rol;

        $user->save();
        return response()->json(['message' => 'Se actualizó a ' . $request->name]);
    }

    //BORRAR
    public function destroy($id)
    {
        try {
            $users = User::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'No se encontró al usuario para eliminarlo.'], Response::HTTP_NOT_FOUND);
        }

        $users->delete();
        return response()->json(['message' => 'El usuario ' . $users->name . ' ha sido borrado exitosamente.']);
    }


    //LOGIN
    public function login(Request $request)
    {
        // Valida los datos de entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // La autenticación fue exitosa
            $user = Auth::user();
            $token = $user->createToken('token')->plainTextToken;
            $name = $user->name; // Obtener el 'name' del usuario
            $name = $user->rol->nombreRol; // Obtener el 'nombreRol' del usuario

            return response([
                "token" => $token,
                "name" => $name, // Agregar 'name' a la respuesta
                "nombreRol" => $name, // Agregar 'nombreRol' a la respuesta
            ], Response::HTTP_OK);
        } else {
            // La autenticación falló
            return response()->json(['error' => 'Credenciales no válidas'], Response::HTTP_UNAUTHORIZED);
        }
    }


    // LOGOUT
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Obtener los tokens de acceso activos del usuario
        $tokens = $user->tokens;

        // Iterar sobre los tokens y eliminarlos
        foreach ($tokens as $token) {
            $token->delete();
        }

        return response()->json(['message' => 'Sesión cerrada exitosamente'], 200);
    }


    // MOSTRAR USUARIOS DESCENDENTE
    public function index()
    {
        return User::with('personas', 'rol')->orderBy('updated_at', 'desc')->get();
    }

    // MOSTRAR USUARIOS ASCENDENTE
    public function ascendente()
    {
        return User::with('personas', 'rol')->orderBy('updated_at', 'asc')->get();
    }

    // MOSTRAR USUARIOS EN UN RANGO
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

        $usuariosEnRango = User::with('personas', 'rol')->whereBetween('updated_at', [$fechaInicio, $fechaFin])->get();

        return response()->json(['usuarios' => $usuariosEnRango]);
    }

    // BUSCADOR DE USUARIOS
    public function buscadorUsuarios(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $name = $request->name;

        // Buscar Roles que coincidan con el nombre proporcionado
        $Roles = User::with('personas', 'rol')->where('name', 'LIKE', '%' . $name . '%')->get();

        return response()->json(['Roles' => $Roles]);
    }
}
