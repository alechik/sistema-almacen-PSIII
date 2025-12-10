<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\EnvioPlanta;
use App\Models\EnvioPlantaProducto;
use App\Models\Producto;
use App\Services\PlantaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PedidoPlantaController extends Controller
{
    protected PlantaApiService $plantaApi;

    public function __construct(PlantaApiService $plantaApi)
    {
        $this->plantaApi = $plantaApi;
    }

    /**
     * Formulario para crear un nuevo pedido a planta
     */
    public function create()
    {
        $usuario = Auth::user();
        
        // Obtener almacenes del usuario
        $almacenes = $this->getAlmacenesDelUsuario($usuario);
        
        // Obtener productos disponibles
        $productos = Producto::where('estado', 1)->orderBy('nombre')->get();

        // Verificar conexión con Planta
        $plantaConectada = $this->plantaApi->ping();

        return view('envios-planta.crear-pedido', compact('almacenes', 'productos', 'plantaConectada'));
    }

    /**
     * Enviar pedido a PlantaCruds
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'almacen_id' => 'required|exists:almacens,id',
            'fecha_requerida' => 'required|date|after_or_equal:today',
            'hora_requerida' => 'nullable|string',
            'observaciones' => 'nullable|string|max:500',
            'productos' => 'required|array|min:1',
            'productos.*.nombre' => 'required|string',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.peso_unitario' => 'nullable|numeric|min:0',
            'productos.*.precio_unitario' => 'nullable|numeric|min:0',
        ]);

        $usuario = Auth::user();
        $almacen = Almacen::findOrFail($validated['almacen_id']);

        // Generar código único para el pedido
        $codigo = 'PED-ALM-' . strtoupper(Str::random(8));

        // Calcular totales
        $totalCantidad = 0;
        $totalPeso = 0;
        $totalPrecio = 0;
        $productosData = [];

        foreach ($validated['productos'] as $prod) {
            $cantidad = $prod['cantidad'];
            $pesoUnit = $prod['peso_unitario'] ?? 0;
            $precioUnit = $prod['precio_unitario'] ?? 0;
            
            $totalCantidad += $cantidad;
            $totalPeso += $cantidad * $pesoUnit;
            $totalPrecio += $cantidad * $precioUnit;

            $productosData[] = [
                'producto_nombre' => $prod['nombre'],
                'cantidad' => $cantidad,
                'peso_unitario' => $pesoUnit,
                'precio_unitario' => $precioUnit,
                'total_peso' => $cantidad * $pesoUnit,
                'total_precio' => $cantidad * $precioUnit,
            ];
        }

        // Preparar datos para enviar a PlantaCruds
        $pedidoData = [
            'codigo_origen' => $codigo,
            'almacen_destino' => $almacen->nombre,
            'almacen_destino_lat' => $almacen->latitud,
            'almacen_destino_lng' => $almacen->longitud,
            'almacen_destino_direccion' => $almacen->ubicacion,
            'solicitante_id' => $usuario->id,
            'solicitante_nombre' => $usuario->full_name ?? $usuario->name,
            'solicitante_email' => $usuario->email,
            'fecha_requerida' => $validated['fecha_requerida'],
            'hora_requerida' => $validated['hora_requerida'],
            'observaciones' => $validated['observaciones'],
            'total_cantidad' => $totalCantidad,
            'total_peso' => $totalPeso,
            'total_precio' => $totalPrecio,
            'productos' => $productosData,
            // URL de callback para webhooks
            'webhook_url' => url('/api/webhook/planta'),
        ];

        // Enviar a PlantaCruds
        $response = $this->plantaApi->crearPedido($pedidoData);

        if ($response && isset($response['success']) && $response['success']) {
            // Crear registro local del envío con datos de Planta
            $envioLocal = EnvioPlanta::create([
                'envio_planta_id' => $response['envio_id'] ?? null,
                'codigo' => $response['codigo'] ?? $codigo,
                'almacen_id' => $almacen->id,
                'solicitante_id' => $usuario->id,
                'estado' => $response['estado'] ?? 'pendiente',
                'fecha_creacion' => now(),
                'fecha_estimada_entrega' => $validated['fecha_requerida'],
                'hora_estimada' => $validated['hora_requerida'],
                'total_cantidad' => $totalCantidad,
                'total_peso' => $totalPeso,
                'total_precio' => $totalPrecio,
                'observaciones' => $validated['observaciones'],
                // Coordenadas de origen (Planta)
                'origen_lat' => $response['origen_lat'] ?? null,
                'origen_lng' => $response['origen_lng'] ?? null,
                'origen_direccion' => $response['origen_direccion'] ?? 'Planta Principal',
                // Coordenadas de destino
                'destino_lat' => $response['destino_lat'] ?? $almacen->latitud,
                'destino_lng' => $response['destino_lng'] ?? $almacen->longitud,
                'destino_direccion' => $response['destino_direccion'] ?? $almacen->ubicacion,
                'visto' => true,
                'sincronizado_at' => now(),
            ]);

            // Guardar productos
            foreach ($productosData as $prod) {
                EnvioPlantaProducto::create([
                    'envio_planta_id' => $envioLocal->id,
                    'producto_nombre' => $prod['producto_nombre'],
                    'cantidad' => $prod['cantidad'],
                    'peso_unitario' => $prod['peso_unitario'],
                    'precio_unitario' => $prod['precio_unitario'],
                    'total_peso' => $prod['total_peso'],
                    'total_precio' => $prod['total_precio'],
                ]);
            }

            return redirect()->route('envios-planta.mis-envios')
                ->with('success', "¡Pedido enviado exitosamente! Código: {$envioLocal->codigo}");
        }

        // Si falla la conexión con Planta, crear registro local pendiente de sincronización
        $envioLocal = EnvioPlanta::create([
            'codigo' => $codigo,
            'almacen_id' => $almacen->id,
            'solicitante_id' => $usuario->id,
            'estado' => 'pendiente',
            'fecha_creacion' => now(),
            'fecha_estimada_entrega' => $validated['fecha_requerida'],
            'hora_estimada' => $validated['hora_requerida'],
            'total_cantidad' => $totalCantidad,
            'total_peso' => $totalPeso,
            'total_precio' => $totalPrecio,
            'observaciones' => $validated['observaciones'] . "\n[Pendiente de sincronización con Planta]",
            'destino_lat' => $almacen->latitud,
            'destino_lng' => $almacen->longitud,
            'destino_direccion' => $almacen->ubicacion,
            'visto' => true,
        ]);

        // Guardar productos
        foreach ($productosData as $prod) {
            EnvioPlantaProducto::create([
                'envio_planta_id' => $envioLocal->id,
                'producto_nombre' => $prod['producto_nombre'],
                'cantidad' => $prod['cantidad'],
                'peso_unitario' => $prod['peso_unitario'],
                'precio_unitario' => $prod['precio_unitario'],
                'total_peso' => $prod['total_peso'],
                'total_precio' => $prod['total_precio'],
            ]);
        }

        $errorMsg = $response['message'] ?? 'No se pudo conectar con Planta';
        
        return redirect()->route('envios-planta.mis-envios')
            ->with('warning', "Pedido guardado localmente (Código: {$codigo}). Error al enviar a Planta: {$errorMsg}");
    }

    /**
     * Ver mis envíos (del usuario actual)
     */
    public function misEnvios(Request $request)
    {
        $usuario = Auth::user();

        $query = EnvioPlanta::with(['almacen', 'incidentes'])
            ->where('solicitante_id', $usuario->id)
            ->orderBy('created_at', 'desc');

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $envios = $query->paginate(15);

        // Estadísticas
        $estadisticas = [
            'total' => EnvioPlanta::where('solicitante_id', $usuario->id)->count(),
            'pendientes' => EnvioPlanta::where('solicitante_id', $usuario->id)
                ->whereIn('estado', ['pendiente', 'asignado', 'aceptado'])->count(),
            'en_transito' => EnvioPlanta::where('solicitante_id', $usuario->id)
                ->where('estado', 'en_transito')->count(),
            'entregados' => EnvioPlanta::where('solicitante_id', $usuario->id)
                ->where('estado', 'entregado')->count(),
        ];

        return view('envios-planta.mis-envios', compact('envios', 'estadisticas'));
    }

    /**
     * Obtener almacenes del usuario
     */
    protected function getAlmacenesDelUsuario($usuario)
    {
        if ($usuario->hasRole('propietario')) {
            return Almacen::where('user_id', $usuario->id)->get();
        }

        if ($usuario->hasRole('administrador')) {
            $propietarioId = $usuario->user_id;
            return Almacen::where('user_id', $propietarioId)->get();
        }

        return $usuario->almacenes ?? collect();
    }
}

