<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Services\TrazabilidadIntegrationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PedidoController extends Controller
{
    protected TrazabilidadIntegrationService $trazabilidadService;

    private $proveedores = [
        ['id' => 1, 'nombre' => 'Proveedor 1'],
        ['id' => 2, 'nombre' => 'Proveedor 2'],
        ['id' => 3, 'nombre' => 'Proveedor 3'],
    ];

    public function __construct(TrazabilidadIntegrationService $trazabilidadService)
    {
        $this->trazabilidadService = $trazabilidadService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // PROPIETARIO → ve los pedidos de todos sus administradores Y sus propios pedidos
        if ($user->hasRole('propietario')) {

            // IDs de todos sus administradores
            $admins = User::role('administrador')
                ->where('user_id', $user->id)
                ->pluck('id');

            // Pedidos de administradores + pedidos donde el propietario es el administrador
            $pedidos = Pedido::where(function($query) use ($admins, $user) {
                    $query->whereIn('administrador_id', $admins)
                          ->orWhere('administrador_id', $user->id);
                })
                ->with(['almacen', 'administrador'])
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        // ADMINISTRADOR → SOLO sus pedidos
        else if ($user->hasRole('administrador')) {

            $pedidos = Pedido::where('administrador_id', $user->id)
                ->with(['almacen', 'administrador'])
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        // OPERADOR → pedidos donde participa
        else if ($user->hasRole('operador')) {

            $pedidos = Pedido::where('operador_id', $user->id)
                ->with(['almacen', 'administrador'])
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        // TRANSPORTISTA → pedidos donde participa
        else if ($user->hasRole('transportista')) {

            $pedidos = Pedido::where('transportista_id', $user->id)
                ->with(['almacen', 'administrador'])
                ->orderBy('id', 'desc')
                ->paginate(10);
        }

        // OTROS (por si existiera un rol raro)
        else {
            $pedidos = collect(); // vacío
        }

        return view('pedidos.index', [
            'pedidos' => $pedidos,
            'proveedores' => $this->proveedores
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        
        // Permitir acceso a propietarios y administradores
        if (!$user->hasAnyRole(['propietario', 'administrador'])) {
            abort(403, 'No tienes permisos para crear pedidos');
        }

        $propietarioId = $user->user_id ?? $user->id;

        $lastId = Pedido::max('id') ?? 0;

        // Si es propietario, mostrar sus almacenes creados
        // Si es administrador, mostrar almacenes asignados
        if ($user->hasRole('propietario')) {
            $almacenes = Almacen::where('user_id', $user->id)->get();
        } else {
            $almacenes = $user->almacenes;
        }

        // Obtener productos desde Trazabilidad
        $productosTrazabilidad = $this->getProductosFromTrazabilidad();

        // Proveedor fijo: Planta (ID 1)
        $proveedorPlanta = ['id' => 1, 'nombre' => 'Planta'];

        return view('pedidos.create', compact('almacenes', 'lastId', 'productosTrazabilidad', 'proveedorPlanta'));
    }

    /**
     * Obtiene productos desde la API de Trazabilidad
     */
    private function getProductosFromTrazabilidad(): array
    {
        $trazabilidadUrl = env('TRAZABILIDAD_API_URL', 'http://localhost:8000/api');
        
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->get("{$trazabilidadUrl}/products");

            if ($response->successful()) {
                $data = $response->json();
                // Si es una respuesta paginada, obtener los datos
                if (isset($data['data'])) {
                    return $data['data'];
                }
                // Si es un array directo
                if (is_array($data)) {
                    return $data;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error al obtener productos desde Trazabilidad', [
                'error' => $e->getMessage()
            ]);
        }

        return [];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_comprobante' => 'required|string',
            'fecha' => 'required|date',
            'almacen_id' => 'required|exists:almacens,id',
            'proveedor_id' => 'nullable', // Fijo como Planta (1)
            'operador_id' => 'nullable', // Ya no es necesario
            'transportista_id' => 'nullable', // Ya no es necesario
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required', // Ya no valida contra tabla local
            'productos.*.producto_nombre' => 'required|string', // Nombre del producto desde Trazabilidad
            'productos.*.cantidad' => 'required|numeric|min:0.01',
        ]);

        $user = Auth::user();
        $propietarioId = $user->user_id ?? $user->id;

        $lastPedido = Pedido::orderBy('id', 'desc')->first();
        $newId = $lastPedido ? $lastPedido->id + 1 : 1;

        $codigo = 'P' . ($propietarioId * 1000000 + $newId);

        $pedido = Pedido::create([
            'codigo_comprobante' => $codigo,
            'fecha' => $request->fecha,
            'fecha_min' => $request->fecha, // Usar la misma fecha como fecha_min
            'fecha_max' => $request->fecha, // Usar la misma fecha como fecha_max
            'estado' => Pedido::EMITIDO,
            'almacen_id' => $request->almacen_id,
            'proveedor_id' => 1, // Fijo: Planta
            'operador_id' => null, // Ya no es necesario
            'transportista_id' => null, // Ya no es necesario
            'administrador_id' => $user->id,
        ]);

        // Crear productos del pedido
        // Nota: Los productos vienen desde Trazabilidad, así que guardamos el nombre
        // Si el producto existe localmente, usamos su ID, si no, guardamos null y el nombre
        foreach ($request->productos as $item) {
            $productoId = null;
            $productoTrazabilidadId = null;
            
            // El producto_id viene de Trazabilidad
            if (isset($item['producto_id']) && is_numeric($item['producto_id'])) {
                $productoTrazabilidadId = $item['producto_id'];
                
                // Intentar buscar producto local por ID de Trazabilidad
                $productoLocal = Producto::find($item['producto_id']);
                if ($productoLocal) {
                    $productoId = $productoLocal->id;
                }
            }
            
            // Si no se encontró por ID, buscar por nombre
            if (!$productoId && isset($item['producto_nombre'])) {
                $productoLocal = Producto::where('nombre', $item['producto_nombre'])->first();
                if ($productoLocal) {
                    $productoId = $productoLocal->id;
                }
            }
            
            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $productoId, // Puede ser null si viene de Trazabilidad
                'producto_trazabilidad_id' => $productoTrazabilidadId, // ID de Trazabilidad
                'producto_nombre' => $item['producto_nombre'] ?? null, // Nombre desde Trazabilidad
                'cantidad' => $item['cantidad'],
            ]);
        }

        // Si es propietario, enviar automáticamente a Trazabilidad
        if ($user->hasRole('propietario')) {
            try {
                $result = $this->trazabilidadService->sendPedidoToTrazabilidad($pedido);
                
                return redirect()->route('pedidos.index')
                    ->with('success', 'Pedido creado y enviado a Trazabilidad exitosamente. Tracking ID: ' . ($result['tracking_id'] ?? 'N/A'));
            } catch (\Exception $e) {
                Log::error('Error al enviar pedido a Trazabilidad', [
                    'pedido_id' => $pedido->id,
                    'error' => $e->getMessage()
                ]);
                
                return redirect()->route('pedidos.index')
                    ->with('warning', 'Pedido creado exitosamente, pero hubo un error al enviarlo a Trazabilidad: ' . $e->getMessage());
            }
        }

        return redirect()->route('pedidos.create')
            ->with('success', 'Pedido creado exitosamente');
    }


    public function show(Pedido $pedido)
    {
        $pedido->load([
            'almacen',
            'operador',
            'transportista',
            'administrador',
            'detalles.producto'
        ]);
        // dd($pedido);

        return view('pedidos.show', [
            'pedido' => $pedido,
            'proveedores' => $this->proveedores
        ]);
    }

    public function edit(Pedido $pedido)
    {
        $pedido->load(['detalles.producto']);

        $admin = Auth::user();

        $propietarioId = $admin->hasRole('propietario')
            ? $admin->id
            : $admin->user_id;

        // Almacenes del propietario
        $almacenes = Almacen::where('user_id', $propietarioId)->get();

        // Obtener productos desde Trazabilidad
        $productosTrazabilidad = $this->getProductosFromTrazabilidad();

        // Proveedor fijo: Planta (ID 1)
        $proveedorPlanta = ['id' => 1, 'nombre' => 'Planta'];

        return view('pedidos.edit', compact(
            'pedido',
            'almacenes',
            'productosTrazabilidad',
            'proveedorPlanta'
        ) + ['proveedores' => $this->proveedores]);
    }

    public function update(Request $request, Pedido $pedido)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'fecha_min' => 'required|date',
            'fecha_max' => 'required|date|after_or_equal:fecha_min',
            'almacen_id' => 'required|exists:almacens,id',
            'proveedor_id' => 'nullable', // Fijo como Planta (1)
            'operador_id' => 'nullable', // Ya no es necesario
            'transportista_id' => 'nullable', // Ya no es necesario
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required', // Ya no valida contra tabla local
            'productos.*.producto_nombre' => 'required|string', // Nombre del producto desde Trazabilidad
            'productos.*.cantidad' => 'required|numeric|min:0.01',
        ]);

        // CABECERA
        $pedido->update([
            'fecha' => $request->fecha,
            'fecha_min' => $request->fecha, // Usar la misma fecha como fecha_min
            'fecha_max' => $request->fecha, // Usar la misma fecha como fecha_max
            'almacen_id' => $request->almacen_id,
            'proveedor_id' => 1, // Fijo: Planta
            'operador_id' => null, // Ya no es necesario
            'transportista_id' => null, // Ya no es necesario
        ]);

        // DETALLE (Reemplazo total)
        DetallePedido::where('pedido_id', $pedido->id)->delete();

        foreach ($request->productos as $item) {
            $productoId = null;
            $productoTrazabilidadId = null;
            
            // El producto_id viene de Trazabilidad
            if (isset($item['producto_id']) && is_numeric($item['producto_id'])) {
                $productoTrazabilidadId = $item['producto_id'];
                
                // Intentar buscar producto local por ID de Trazabilidad
                $productoLocal = Producto::find($item['producto_id']);
                if ($productoLocal) {
                    $productoId = $productoLocal->id;
                }
            }
            
            // Si no se encontró por ID, buscar por nombre
            if (!$productoId && isset($item['producto_nombre'])) {
                $productoLocal = Producto::where('nombre', $item['producto_nombre'])->first();
                if ($productoLocal) {
                    $productoId = $productoLocal->id;
                }
            }
            
            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $productoId, // Puede ser null si viene de Trazabilidad
                'producto_trazabilidad_id' => $productoTrazabilidadId, // ID de Trazabilidad
                'producto_nombre' => $item['producto_nombre'] ?? null, // Nombre desde Trazabilidad
                'cantidad' => $item['cantidad'],
            ]);
        }

        return redirect()->route('pedidos.index')
            ->with('success', 'Pedido actualizado correctamente.');
    }

    public function confirmar(Pedido $pedido)
    {
        if ($pedido->estado != Pedido::EMITIDO) {
            return back()->with('error', 'Solo los pedidos EMITIDOS pueden ser confirmados.');
        }

        $pedido->update([
            'estado' => Pedido::CONFIRMADO
        ]);

        return back()->with('success', 'Pedido confirmado correctamente.');
    }

    public function anular(Pedido $pedido)
    {
        if ($pedido->estado == Pedido::TERMINADO) {
            return back()->with('error', 'No se puede anular un pedido TERMINADO.');
        }

        $pedido->update([
            'estado' => Pedido::ANULADO
        ]);

        return back()->with('success', 'Pedido anulado correctamente.');
    }

    // Obtener operadores y transportistas del almacén
    public function getUsuariosPorAlmacen($almacenId)
    {
        $operadores = User::role('operador')
            ->whereHas('almacenes', fn($q) => $q->where('almacen_id', $almacenId))
            ->select('id', 'full_name')
            ->get();

        $transportistas = User::role('transportista')
            ->whereHas('almacenes', fn($q) => $q->where('almacen_id', $almacenId))
            ->select('id', 'full_name')
            ->get();

        return response()->json([
            'operadores' => $operadores,
            'transportistas' => $transportistas
        ]);
    }

    // Obtener productos según proveedor
    public function getProductosPorProveedor($proveedorId)
    {
        $productos = Producto::where('proveedor_id', $proveedorId)
            ->where('estado', 1)
            ->select('id', 'nombre')
            ->get();

        return response()->json($productos);
    }



    public function generarPDF(Pedido $pedido)
    {
        $usuario = Auth::user();
        $empresa = $usuario->hasRole('propietario')
            ? $usuario
            : $usuario->parent;

        $pedido->load(['almacen', 'operador', 'transportista', 'administrador', 'detalles.producto']);
        $proveedores = $this->proveedores;

        $pdf = Pdf::loadView('pedidos.comprobante-pdf', compact('pedido', 'proveedores', 'empresa'))
            ->setPaper('letter', 'portrait');

        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();

        $w = $canvas->get_width();
        $h = $canvas->get_height();

        // === TEXTO IZQUIERDA DEL FOOTER (admin + fecha) ===
        $canvas->page_text(
            25,                   // X (parte izquierda)
            $h - 35,              // Y (bien abajo)
            "Solicitado por: " . ($pedido->administrador->full_name ?? '-') .
                "    |    Fecha: " . now()->format('d/m/Y'),
            null,
            9,
            [0, 0, 0]
        );

        // === NUMERACIÓN A LA DERECHA ===
        $canvas->page_text(
            $w - 120,            // X (derecha)
            $h - 35,             // misma altura exacta
            "Página {PAGE_NUM} de {PAGE_COUNT}",
            null,
            9,
            [0, 0, 0]
        );

        return $pdf->stream("pedido-{$pedido->codigo_comprobante}.pdf");
    }

    /**
     * Enviar pedido a Trazabilidad manualmente
     */
    public function enviarATrazabilidad(Pedido $pedido)
    {
        $user = Auth::user();

        // Solo propietario o administrador pueden enviar
        if (!$user->hasAnyRole(['propietario', 'administrador'])) {
            abort(403, 'No tienes permisos para enviar pedidos a Trazabilidad');
        }

        // Verificar que el pedido no haya sido enviado ya
        if ($pedido->enviado_a_trazabilidad) {
            return back()->with('warning', 'Este pedido ya fue enviado a Trazabilidad');
        }

        try {
            // Cargar todas las relaciones necesarias
            $pedido->load([
                'almacen',
                'administrador',
                'operador',
                'transportista',
                'detalles.producto'
            ]);
            
            // Verificar que el pedido tenga productos
            if ($pedido->detalles->isEmpty()) {
                return back()->with('error', 'El pedido no tiene productos. No se puede enviar a Trazabilidad.');
            }
            
            Log::info('Iniciando envío de pedido a Trazabilidad', [
                'pedido_id' => $pedido->id,
                'codigo' => $pedido->codigo_comprobante,
                'productos_count' => $pedido->detalles->count()
            ]);
            
            $result = $this->trazabilidadService->sendPedidoToTrazabilidad($pedido);
            
            Log::info('Pedido enviado exitosamente', [
                'pedido_id' => $pedido->id,
                'tracking_id' => $result['tracking_id'] ?? null
            ]);
            
            return back()->with('success', 'Pedido enviado a Trazabilidad exitosamente. Tracking ID: ' . ($result['tracking_id'] ?? 'N/A'));
        } catch (\Exception $e) {
            Log::error('Error al enviar pedido a Trazabilidad', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return back()->with('error', 'Error al enviar pedido a Trazabilidad: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar seguimiento en tiempo real de pedidos del almacén
     */
    public function seguimiento()
    {
        $user = Auth::user();
        
        // Obtener TODOS los pedidos del propietario/administrador (sin filtrar por estado)
        // Luego filtraremos solo los que tienen envíos asociados
        if ($user->hasRole('propietario')) {
            $admins = User::role('administrador')
                ->where('user_id', $user->id)
                ->pluck('id');
            
            $pedidos = Pedido::where(function($query) use ($admins, $user) {
                    $query->whereIn('administrador_id', $admins)
                          ->orWhere('administrador_id', $user->id);
                })
                ->whereNotIn('estado', [
                    Pedido::CANCELADO,
                    Pedido::ANULADO,
                    Pedido::RECHAZADO_TRAZABILIDAD
                ]) // Excluir solo estados definitivamente cancelados/anulados
                ->with(['almacen'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $pedidos = Pedido::where('administrador_id', $user->id)
                ->whereNotIn('estado', [
                    Pedido::CANCELADO,
                    Pedido::ANULADO,
                    Pedido::RECHAZADO_TRAZABILIDAD
                ]) // Excluir solo estados definitivamente cancelados/anulados
                ->with(['almacen'])
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        // Extraer envio_id de cada pedido desde observaciones o pedido_entregas
        $pedidosConEnvio = $pedidos->map(function($pedido) {
            $envioId = null;
            $envioCodigo = null;
            
            // Intentar obtener desde pedido_entregas primero
            $entrega = DB::table('pedido_entregas')
                ->where('pedido_id', $pedido->id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($entrega) {
                $envioId = $entrega->envio_id;
                $envioCodigo = $entrega->envio_codigo;
            } else {
                // Intentar extraer de observaciones (múltiples patrones posibles)
                $observaciones = $pedido->observaciones ?? '';
                
                // Patrón 1: "Envío ID (plantaCruds): 123"
                if (preg_match('/Envío ID\s*\(?plantaCruds\)?:\s*(\d+)/i', $observaciones, $matches)) {
                    $envioId = (int)$matches[1];
                }
                
                // Patrón 2: "envio_id: 123" o "envio: 123"
                if (!$envioId && preg_match('/envio[_\s]*id[:\s]+(\d+)/i', $observaciones, $matches)) {
                    $envioId = (int)$matches[1];
                }
                
                // Código de envío
                if (preg_match('/Código Envío:\s*([A-Z0-9-]+)/i', $observaciones, $matches)) {
                    $envioCodigo = $matches[1];
                } else if (preg_match('/envio[_\s]*codigo[:\s]+([A-Z0-9-]+)/i', $observaciones, $matches)) {
                    $envioCodigo = $matches[1];
                }
            }
            
            return [
                'pedido_id' => $pedido->id,
                'pedido_codigo' => $pedido->codigo_comprobante,
                'almacen_nombre' => $pedido->almacen->nombre ?? 'N/A',
                'envio_id' => $envioId,
                'envio_codigo' => $envioCodigo,
                'estado' => $pedido->estado,
            ];
        })->filter(function($item) {
            // Solo pedidos que tienen envío asociado
            return $item['envio_id'] !== null;
        });
        
        // Log para depuración (temporal)
        \Log::info('Pedidos con envío encontrados', [
            'total_pedidos' => $pedidos->count(),
            'pedidos_con_envio' => $pedidosConEnvio->count(),
            'pedidos_ids' => $pedidosConEnvio->pluck('pedido_id')->toArray(),
            'envios_ids' => $pedidosConEnvio->pluck('envio_id')->toArray(),
        ]);
        
        // Extraer los envio_ids para pasarlos a la vista (para el JavaScript)
        $pedidoEnvioIds = $pedidosConEnvio->pluck('envio_id')->filter()->unique()->values()->toArray();
        
        // URL de la API de plantaCruds
        $plantaCrudsApiUrl = env('PLANTA_CRUDS_API_URL', 'http://localhost:8001');
        
        return view('pedidos.seguimiento', compact('pedidosConEnvio', 'plantaCrudsApiUrl', 'pedidoEnvioIds'));
    }

    /**
     * Listar pedidos con documentación entregada
     */
    public function documentacion()
    {
        $user = Auth::user();
        
        // Obtener pedidos del propietario/administrador que tienen documentación
        if ($user->hasRole('propietario')) {
            $admins = User::role('administrador')
                ->where('user_id', $user->id)
                ->pluck('id');
            
            $pedidosConDocumentos = DB::table('pedido_entregas')
                ->join('pedidos', 'pedido_entregas.pedido_id', '=', 'pedidos.id')
                ->where(function($query) use ($admins, $user) {
                    $query->whereIn('pedidos.administrador_id', $admins)
                          ->orWhere('pedidos.administrador_id', $user->id);
                })
                ->select(
                    'pedidos.id',
                    'pedidos.codigo_comprobante',
                    'pedidos.fecha',
                    'pedidos.estado',
                    'pedido_entregas.envio_id',
                    'pedido_entregas.envio_codigo',
                    'pedido_entregas.fecha_entrega',
                    'pedido_entregas.transportista_nombre',
                    'pedido_entregas.documentos',
                    'pedido_entregas.created_at'
                )
                ->orderBy('pedido_entregas.created_at', 'desc')
                ->get()
                ->map(function ($pedido) {
                    // Decodificar documentos JSON
                    $pedido->documentos = json_decode($pedido->documentos, true) ?? [];
                    return $pedido;
                });
        } else {
            $pedidosConDocumentos = DB::table('pedido_entregas')
                ->join('pedidos', 'pedido_entregas.pedido_id', '=', 'pedidos.id')
                ->where('pedidos.administrador_id', $user->id)
                ->select(
                    'pedidos.id',
                    'pedidos.codigo_comprobante',
                    'pedidos.fecha',
                    'pedidos.estado',
                    'pedido_entregas.envio_id',
                    'pedido_entregas.envio_codigo',
                    'pedido_entregas.fecha_entrega',
                    'pedido_entregas.transportista_nombre',
                    'pedido_entregas.documentos',
                    'pedido_entregas.created_at'
                )
                ->orderBy('pedido_entregas.created_at', 'desc')
                ->get()
                ->map(function ($pedido) {
                    $pedido->documentos = json_decode($pedido->documentos, true) ?? [];
                    return $pedido;
                });
        }
        
        return view('pedidos.documentacion.index', compact('pedidosConDocumentos'));
    }

    /**
     * Mostrar documentos de un pedido específico
     */
    public function documentacionShow(Pedido $pedido)
    {
        $user = Auth::user();
        
        // Verificar que el usuario tenga acceso a este pedido
        if ($user->hasRole('propietario')) {
            $admins = User::role('administrador')
                ->where('user_id', $user->id)
                ->pluck('id');
            
            if (!in_array($pedido->administrador_id, $admins->toArray()) && $pedido->administrador_id != $user->id) {
                abort(403, 'No tienes acceso a este pedido');
            }
        } else {
            if ($pedido->administrador_id != $user->id) {
                abort(403, 'No tienes acceso a este pedido');
            }
        }
        
        // Obtener todas las documentaciones de este pedido
        $documentaciones = DB::table('pedido_entregas')
            ->where('pedido_id', $pedido->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($doc) {
                $doc->documentos = json_decode($doc->documentos, true) ?? [];
                return $doc;
            });
        
        return view('pedidos.documentacion.show', compact('pedido', 'documentaciones'));
    }

    /**
     * Descargar un documento específico
     */
    public function descargarDocumento(Pedido $pedido, $tipo)
    {
        $user = Auth::user();
        
        // Verificar acceso
        if ($user->hasRole('propietario')) {
            $admins = User::role('administrador')
                ->where('user_id', $user->id)
                ->pluck('id');
            
            if (!in_array($pedido->administrador_id, $admins->toArray()) && $pedido->administrador_id != $user->id) {
                abort(403);
            }
        } else {
            if ($pedido->administrador_id != $user->id) {
                abort(403);
            }
        }
        
        try {
            // Validar tipo de documento
            $tiposValidos = ['propuesta_vehiculos_path', 'nota_entrega_path', 'trazabilidad_completa_path'];
            if (!in_array($tipo, $tiposValidos)) {
                abort(400, 'Tipo de documento inválido');
            }
            
            // Obtener la documentación más reciente del pedido
            $documentacion = DB::table('pedido_entregas')
                ->where('pedido_id', $pedido->id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$documentacion) {
                abort(404, 'Documentación no encontrada');
            }
            
            // Obtener la ruta del documento desde el JSON
            $documentos = json_decode($documentacion->documentos, true) ?? [];
            
            // Mapear tipos de documento
            $tipoMap = [
                'propuesta_vehiculos_path' => 'propuesta_vehiculos',
                'nota_entrega_path' => 'nota_entrega',
                'trazabilidad_completa_path' => 'trazabilidad_completa',
            ];
            
            $tipoDocumento = $tipoMap[$tipo] ?? $tipo;
            $rutaArchivo = $documentos[$tipoDocumento] ?? null;
            
            if (!$rutaArchivo || !Storage::exists($rutaArchivo)) {
                abort(404, 'Archivo no encontrado');
            }
            
            $contenido = Storage::get($rutaArchivo);
            $nombreArchivo = basename($rutaArchivo);
            
            return response($contenido, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $nombreArchivo . '"');
        } catch (\Exception $e) {
            Log::error('Error descargando documento', [
                'pedido_id' => $pedido->id,
                'tipo' => $tipo,
                'error' => $e->getMessage()
            ]);
            abort(500, 'Error al descargar documento');
        }
    }
}
