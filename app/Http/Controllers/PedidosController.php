<?php

namespace App\Http\Controllers;

use App\Models\Pedidos;
use App\Models\Personas;
use App\Models\Productos;
use App\Models\Empresas;
use App\Models\User;
use Illuminate\Http\Request;

use FPDF;

use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PedidosController extends Controller
{

    //AGREGAR
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombreEmpresa' => ['required', 'string', 'max:50'],
            'nombrePersona' => ['required', 'string', 'max:50'],
            'fechaPedido' => ['required', 'date'],
            'fechaEntrega' => ['date', 'nullable'],
            'productos' => ['required', 'array'],
            'anticipo' => ['nullable', 'integer'],
            'saldo' => ['nullable', 'integer'],
            'montoTotal' => ['required', 'integer'],
            'historial' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_NOT_FOUND);
        }

        // Extraer el nombre de la empresa de la entrada
        $nombreEmpresa = $request->nombreEmpresa;

        // Verificar si la empresa existe en la base de datos
        $existingEmpresa = Empresas::where(['nombreEmpresa' => $nombreEmpresa])->first();

        if (!$existingEmpresa) {
            return response()->json(['error' => 'La empresa no existe en la base de datos.'], Response::HTTP_NOT_FOUND);
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
                ->whereHas('empresas', function ($query) use ($nombreEmpresa) {
                    $query->where('nombreEmpresa', $nombreEmpresa);
                })
                ->first();
        }

        if (!$existingPerson) {
            return response()->json(['error' => 'La persona no existe en la empresa correspondiente o en la base de datos.'], Response::HTTP_NOT_FOUND);
        }

        // Verificar cada producto antes de crear el pedido
        foreach ($request->productos as $productoData) {
            $nombreProducto = $productoData['nombreProducto'];
            $categoriaProducto = $productoData['categoria'];
            $cantidad = $productoData['cantidad'];
            $descripcion = $productoData['descripcion'];

            $producto = Productos::where('nombreProducto', $nombreProducto)
                ->whereHas('categoria', function ($query) use ($categoriaProducto) {
                    $query->where('id_categoria', $categoriaProducto);
                })
                ->first();

            if (!$producto) {
                return response()->json(['error' => 'El producto no existe en la categoría proporcionada. No se creó el pedido.'], Response::HTTP_NOT_FOUND);
            }
        }


        $pedido = new Pedidos;
        $pedido->fechaPedido    = $request->fechaPedido;
        $pedido->fechaEntrega   = $request->fechaEntrega;
        $pedido->anticipo       = $request->anticipo;
        $pedido->saldo          = $request->saldo;
        $pedido->montoTotal     = $request->montoTotal;
        $pedido->historial      = $request->historial;
        $pedido->id_empresa     = $existingEmpresa->id_empresa;
        $pedido->id_persona     = $existingPerson->id_persona;
        $pedido->estadoPedido = 0; // Establecer el estado a 0

        $pedido->save();

        // Attach products to the pedido using their names and categories
        $productos = $request->input('productos');

        foreach ($productos as $productoData) {
            $nombreProducto = $productoData['nombreProducto'];
            $categoriaProducto = $productoData['categoria'];
            $cantidad = $productoData['cantidad'];
            $descripcion = $productoData['descripcion'];

            $producto = Productos::where('nombreProducto', $nombreProducto)
                ->whereHas('categoria', function ($query) use ($categoriaProducto) {
                    $query->where('id_categoria', $categoriaProducto);
                })
                ->first();

            if (!$producto) {
                return response()->json(['error' => 'El producto no existe en la categoría proporcionada.'], Response::HTTP_NOT_FOUND);
            }

            // Asigna el producto al pedido con la cantidad correspondiente en la tabla intermedia
            $pedido->productos()->attach([
                $producto->id_producto => [
                    'cantidad' => $cantidad,
                    'descripcion' => $descripcion
                ]
            ]);
        }

        return response()->json(['pedido' => $pedido], Response::HTTP_CREATED);
    }


    //MOSTRAR
    public function show($id)
    {
        $pedido = Pedidos::with(['persona', 'productos' => function ($query) {
            $query->withPivot('cantidad');
        }])->find($id);

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['pedido' => $pedido], Response::HTTP_OK);
    }

    //ACTUALIZAR
    public function update(Request $request, $id)
    {
        // Asegurar de que el pedido exista en la base de datos
        $pedido = Pedidos::find($id);

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'fechaEntrega' => ['date', 'nullable'],
            'historial' => ['string', 'nullable'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }


        if ($request->has('fechaEntrega')) {
            $pedido->fechaEntrega = $request->fechaEntrega;
        }

        if ($request->has('descripcion')) {
            $pedido->descripcion = $request->descripcion;
        }

        if ($request->has('historial')) {
            $pedido->historial = $request->historial;
        }

        // Guardar los cambios en el pedido
        $pedido->save();

        return response()->json(['message' => 'El pedido ha sido actualizado exitosamente.'], Response::HTTP_OK);
    }


    //BORRAR
    public function destroy($id)
    {
        // Busca el pedido en la base de datos
        $pedido = Pedidos::find($id);

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        // Elimina los productos asociados al pedido
        $pedido->productos()->detach();

        // Elimina el pedido
        $pedido->delete();

        return response()->json(['message' => 'Pedido eliminado con éxito.'], Response::HTTP_OK);
    }


    public function estadoPago($id)
    {
        // Busca el pedido en la base de datos
        $pedido = Pedidos::find($id);

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        // Si el anticipo es igual al monto total, establece la fecha de entrega en nula
        if ($pedido->anticipo === $pedido->montoTotal) {
            $pedido->fechaEntrega = null;
        } else {
            // Si el anticipo no cubre el monto total, establece la fecha de entrega en la fecha actual
            $pedido->fechaEntrega = now();
        }

        $pedido->estadoPedido = 1; // Establecer el estado a 1

        $pedido->save();

        return response()->json(['message' => 'Pedido pagado.', 'pedido' => $pedido], Response::HTTP_OK);
    }


    public function pdf($id)
    {
        // Busca el pedido en la base de datos
        $pedido = Pedidos::with(['empresa', 'persona', 'productos' => function ($query) {
            $query->withPivot('cantidad', 'descripcion');
        }])->find($id);

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        // Crear un nuevo objeto PDF con FPDF
        $pdf = new FPDF();
        $pdf->AddPage('P', [210, 120]); // Tamaño personalizado
        $pdf->SetFont('Arial', '', 11, 'ISO-8859-1'); // Utiliza la codificación ISO-8859-1

        // Función personalizada para convertir texto a mayúsculas y reemplazar "ñ" por "Ñ"
        function formatText($text)
        {
            $text = mb_strtoupper($text, 'UTF-8');
            return $text;
        }

        // Encabezado "RECIBO"
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 11, formatText("RECIBO"), 0, 'C');

        // Agregar la imagen (ajusta las rutas y dimensiones según tus necesidades)
        $pdf->Image('dark.png', 88, 20, 25); // Cambia las coordenadas y el tamaño

        // Datos del cliente, fecha del pedido y fecha de entrega
        $nombreEmpresa = $pedido->empresa->nombreEmpresa;
        $nombre = $pedido->persona->nombrePersona;
        $primerApellido = $pedido->persona->pApellidoPersona;
        $segundoApellido = $pedido->persona->sApellidoPersona;
        
        $pdf->MultiCell(0, 11, formatText("EMPRESA: " . $pedido->empresa->nombreEmpresa), 0, 'L');

        $cliente = "CLIENTE: ";

        if (!is_null($nombre)) {
            $cliente .= $nombre;
        }

        if (!is_null($primerApellido)) {
            $cliente .= " " . $primerApellido;
        }

        if (!is_null($segundoApellido)) {
            $cliente .= " " . $segundoApellido;
        }

        $pdf->MultiCell(0, 11, formatText($cliente), 0, 'L');
        $pdf->MultiCell(0, 11, formatText("FECHA DEL PEDIDO: " . $pedido->fechaPedido), 0, 'L');
        $pdf->MultiCell(0, 11, formatText("FECHA DE ENTREGA: " . $pedido->fechaEntrega), 0, 'L');

        // Productos y categorías
        $pdf->MultiCell(0, 11, formatText("PRODUCTOS:"), 0, 'L');
        foreach ($pedido->productos as $producto) {
            $categoria = formatText($producto->categoria->nombreCategoria);
            $nombreProducto = formatText($producto->nombreProducto);
            $cantidad = $producto->pivot->cantidad; // Agrega esta línea para obtener la cantidad
            $descripcion = formatText($producto->pivot->descripcion);

            $pdf->MultiCell(0, 11, "\t\t\t\t\t\t" . $cantidad . " "  . $categoria . " " . $nombreProducto . " " . $descripcion , 0, 'L');
        }

        if ($pedido->estadoPedido == 1) {
            $pdf->Image('pagado.png', 60, 90, 40); // Ajusta la ruta, coordenadas y tamaño de la imagen
        }

        // Anticipo, saldo y total
        $pdf->MultiCell(0, 11, formatText("ANTICIPO: " . $pedido->anticipo), 0, 'L');
        $pdf->MultiCell(0, 11, formatText("SALDO: " . $pedido->saldo), 0, 'L');
        $pdf->MultiCell(0, 11, formatText("TOTAL: " . $pedido->montoTotal), 0, 'L');

        

        // Genera el contenido del PDF
        $pdfContent = $pdf->Output("S");

        // Guarda el contenido en un archivo temporal
        $filePath = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($filePath, $pdfContent);

        // Crea una respuesta de archivo binario para el PDF
        $response = new BinaryFileResponse($filePath);

        // Establece las cabeceras de respuesta
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            'ReciboPedido' . $pedido->id . '.pdf'
        );

        // Elimina el archivo temporal después de que se envía
        register_shutdown_function(function () use ($filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        });

        return $response;
    }


    // MOSTRAR PEDIDOS DESCENDENTE
    public function index()
    {
        $pedidos = Pedidos::with(['empresa', 'persona', 'productos' => function ($query) {
            $query->withPivot('cantidad')->with('categoria'); // Cargar la relación de categoría
        }])->orderBy('id_pedido', 'desc')->get();

        return response()->json(['pedidos' => $pedidos], Response::HTTP_OK);
    }

    // MOSTRAR PEDIDOS ASCENDENTE
    public function ascendente()
    {
        $pedidos = Pedidos::with(['empresa', 'persona', 'productos' => function ($query) {
            $query->withPivot('cantidad')->with('categoria'); // Cargar la relación de categoría
        }])->orderBy('id_pedido', 'asc')->get();

        return response()->json(['pedidos' => $pedidos], Response::HTTP_OK);
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

        $pedidosEnRango = Pedidos::with(['empresa', 'persona', 'productos' => function ($query) {
            $query->withPivot('cantidad')->with('categoria'); // Cargar la relación de categoría
        }])->whereBetween('fechaPedido', [$fechaInicio, $fechaFin])->get();

        return response()->json(['pedidos' => $pedidosEnRango]);
    }

}
