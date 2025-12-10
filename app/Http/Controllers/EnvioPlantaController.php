<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\EnvioPlanta;
use App\Models\IncidentePlanta;
use App\Services\PlantaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnvioPlantaController extends Controller
{
    protected PlantaApiService $plantaApi;

    public function __construct(PlantaApiService $plantaApi)
    {
        $this->plantaApi = $plantaApi;
    }

    /**
     * Listado de envíos de planta
     */
    public function index(Request $request)
    {
        $usuario = Auth::user();
        
        // Obtener almacenes del usuario
        $almacenesIds = $this->getAlmacenesDelUsuario($usuario);

        $query = EnvioPlanta::with(['almacen', 'incidentes'])
            ->whereIn('almacen_id', $almacenesIds)
            ->orderBy('created_at', 'desc');

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por almacén
        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                  ->orWhere('transportista_nombre', 'like', "%{$buscar}%")
                  ->orWhere('vehiculo_placa', 'like', "%{$buscar}%");
            });
        }

        $envios = $query->paginate(15);

        // Estadísticas
        $estadisticas = [
            'total' => EnvioPlanta::whereIn('almacen_id', $almacenesIds)->count(),
            'pendientes' => EnvioPlanta::whereIn('almacen_id', $almacenesIds)->where('estado', 'pendiente')->count(),
            'en_transito' => EnvioPlanta::whereIn('almacen_id', $almacenesIds)->where('estado', 'en_transito')->count(),
            'entregados' => EnvioPlanta::whereIn('almacen_id', $almacenesIds)->where('estado', 'entregado')->count(),
            'incidentes' => IncidentePlanta::whereHas('envioPlanta', fn($q) => $q->whereIn('almacen_id', $almacenesIds))
                ->where('estado', 'pendiente')->count(),
        ];

        // Almacenes para el filtro
        $almacenes = Almacen::whereIn('id', $almacenesIds)->get();

        return view('envios-planta.index', compact('envios', 'estadisticas', 'almacenes'));
    }

    /**
     * Ver detalle de un envío
     */
    public function show(EnvioPlanta $envioPlanta)
    {
        $envioPlanta->load(['almacen', 'productos', 'incidentes', 'solicitante']);
        
        // Marcar como visto
        $envioPlanta->marcarComoVisto();

        $datosActualizados = null;

        // Si tiene envio_planta_id, sincronizar datos desde Planta
        if ($envioPlanta->envio_planta_id) {
            $datosActualizados = $this->plantaApi->getEnvio($envioPlanta->envio_planta_id);
            
            // Actualizar datos locales si se obtuvieron de Planta
            if ($datosActualizados && !isset($datosActualizados['error'])) {
                $this->actualizarDatosDesdePlanta($envioPlanta, $datosActualizados);
                $envioPlanta->refresh();
                $envioPlanta->load(['almacen', 'productos', 'incidentes']);
            }
        }

        return view('envios-planta.show', compact('envioPlanta', 'datosActualizados'));
    }

    /**
     * Actualizar datos locales del envío desde Planta
     */
    protected function actualizarDatosDesdePlanta(EnvioPlanta $envio, array $datos): void
    {
        $actualizacion = [];

        // Actualizar estado si cambió
        if (isset($datos['estado']) && $datos['estado'] !== $envio->estado) {
            $actualizacion['estado'] = $datos['estado'];
        }

        // Actualizar datos del transportista
        if (isset($datos['transportista_nombre']) || isset($datos['asignacion'])) {
            $transportista = $datos['asignacion']['transportista'] ?? null;
            $vehiculo = $datos['asignacion']['vehiculo'] ?? null;
            
            if ($transportista) {
                $actualizacion['transportista_nombre'] = $transportista['name'] ?? $datos['transportista_nombre'] ?? null;
                $actualizacion['transportista_telefono'] = $transportista['telefono'] ?? null;
            }
            
            if ($vehiculo) {
                $actualizacion['vehiculo_placa'] = $vehiculo['placa'] ?? null;
                $actualizacion['vehiculo_descripcion'] = ($vehiculo['marca'] ?? '') . ' ' . ($vehiculo['modelo'] ?? '');
            }
        }

        // Actualizar fechas
        if (isset($datos['fecha_asignacion'])) {
            $actualizacion['fecha_asignacion'] = $datos['fecha_asignacion'];
        }
        if (isset($datos['fecha_inicio_transito'])) {
            $actualizacion['fecha_inicio_transito'] = $datos['fecha_inicio_transito'];
        }
        if (isset($datos['fecha_entrega'])) {
            $actualizacion['fecha_entrega'] = $datos['fecha_entrega'];
        }

        // Actualizar coordenadas de origen
        if (isset($datos['origen_lat'])) {
            $actualizacion['origen_lat'] = $datos['origen_lat'];
            $actualizacion['origen_lng'] = $datos['origen_lng'];
            $actualizacion['origen_direccion'] = $datos['origen_direccion'] ?? 'Planta';
        }

        // Marcar como sincronizado
        $actualizacion['sincronizado_at'] = now();

        if (!empty($actualizacion)) {
            $envio->update($actualizacion);
        }
    }

    /**
     * Monitorización en tiempo real (mapa)
     */
    public function monitoreo(EnvioPlanta $envioPlanta)
    {
        $envioPlanta->load(['almacen', 'productos']);

        // Obtener seguimiento GPS
        $seguimiento = $this->plantaApi->getSeguimiento($envioPlanta->envio_planta_id);

        return view('envios-planta.monitoreo', compact('envioPlanta', 'seguimiento'));
    }

    /**
     * Obtener ubicación actual vía AJAX
     */
    public function ubicacionActual(EnvioPlanta $envioPlanta)
    {
        // Actualizar ubicación desde Planta
        $this->plantaApi->actualizarUbicacion($envioPlanta);
        $envioPlanta->refresh();

        return response()->json([
            'success' => true,
            'lat' => $envioPlanta->ubicacion_lat,
            'lng' => $envioPlanta->ubicacion_lng,
            'actualizado_at' => $envioPlanta->ubicacion_actualizada_at?->format('H:i:s'),
            'estado' => $envioPlanta->estado,
        ]);
    }

    /**
     * Ver documento/comprobante del envío
     */
    public function documento(EnvioPlanta $envioPlanta)
    {
        // Redirigir al documento en Planta
        $url = $this->plantaApi->getDocumentoUrl($envioPlanta->envio_planta_id);

        return redirect()->away($url);
    }

    /**
     * Nota de recepción (documento local)
     */
    public function notaRecepcion(EnvioPlanta $envioPlanta)
    {
        if ($envioPlanta->estado !== 'entregado') {
            return back()->with('error', 'El envío aún no ha sido entregado.');
        }

        $envioPlanta->load(['almacen', 'productos']);

        // Generar PDF con DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('envios-planta.nota-recepcion-pdf', [
            'envio' => $envioPlanta
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("nota-recepcion-{$envioPlanta->codigo}.pdf");
    }

    /**
     * Listado de incidentes
     */
    public function incidentes(Request $request)
    {
        $usuario = Auth::user();
        $almacenesIds = $this->getAlmacenesDelUsuario($usuario);

        $query = IncidentePlanta::with(['envioPlanta.almacen'])
            ->whereHas('envioPlanta', fn($q) => $q->whereIn('almacen_id', $almacenesIds))
            ->orderBy('fecha_reporte', 'desc');

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $incidentes = $query->paginate(15);

        $estadisticas = [
            'total' => IncidentePlanta::whereHas('envioPlanta', fn($q) => $q->whereIn('almacen_id', $almacenesIds))->count(),
            'pendientes' => IncidentePlanta::whereHas('envioPlanta', fn($q) => $q->whereIn('almacen_id', $almacenesIds))
                ->where('estado', 'pendiente')->count(),
            'en_proceso' => IncidentePlanta::whereHas('envioPlanta', fn($q) => $q->whereIn('almacen_id', $almacenesIds))
                ->where('estado', 'en_proceso')->count(),
            'resueltos' => IncidentePlanta::whereHas('envioPlanta', fn($q) => $q->whereIn('almacen_id', $almacenesIds))
                ->where('estado', 'resuelto')->count(),
        ];

        return view('envios-planta.incidentes', compact('incidentes', 'estadisticas'));
    }

    /**
     * Ver detalle de un incidente
     */
    public function incidenteShow(IncidentePlanta $incidente)
    {
        $incidente->load(['envioPlanta.almacen', 'envioPlanta.productos']);
        
        // Marcar como visto
        $incidente->marcarComoVisto();

        return view('envios-planta.incidente-show', compact('incidente'));
    }

    /**
     * Sincronizar envíos desde Planta (manual)
     */
    public function sincronizar(Request $request)
    {
        $usuario = Auth::user();
        $almacenesIds = $this->getAlmacenesDelUsuario($usuario);

        $resultadoTotal = ['nuevos' => 0, 'actualizados' => 0, 'errores' => 0];

        foreach ($almacenesIds as $almacenId) {
            $almacen = Almacen::find($almacenId);
            if ($almacen) {
                $resultado = $this->plantaApi->sincronizarEnvios($almacen);
                $resultadoTotal['nuevos'] += $resultado['nuevos'];
                $resultadoTotal['actualizados'] += $resultado['actualizados'];
                $resultadoTotal['errores'] += $resultado['errores'];
            }
        }

        $mensaje = "Sincronización completada: {$resultadoTotal['nuevos']} nuevos, {$resultadoTotal['actualizados']} actualizados";
        
        if ($resultadoTotal['errores'] > 0) {
            $mensaje .= ", {$resultadoTotal['errores']} errores";
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Dashboard de envíos en tránsito
     */
    public function dashboard()
    {
        $usuario = Auth::user();
        $almacenesIds = $this->getAlmacenesDelUsuario($usuario);

        // Envíos activos (en tránsito)
        $enviosEnTransito = EnvioPlanta::with(['almacen'])
            ->whereIn('almacen_id', $almacenesIds)
            ->where('estado', 'en_transito')
            ->get();

        // Envíos pendientes de asignación
        $enviosPendientes = EnvioPlanta::with(['almacen'])
            ->whereIn('almacen_id', $almacenesIds)
            ->whereIn('estado', ['pendiente', 'asignado', 'aceptado'])
            ->orderBy('fecha_estimada_entrega')
            ->take(5)
            ->get();

        // Últimos entregados
        $ultimosEntregados = EnvioPlanta::with(['almacen'])
            ->whereIn('almacen_id', $almacenesIds)
            ->where('estado', 'entregado')
            ->orderBy('fecha_entrega', 'desc')
            ->take(5)
            ->get();

        // Incidentes pendientes
        $incidentesPendientes = IncidentePlanta::with(['envioPlanta.almacen'])
            ->whereHas('envioPlanta', fn($q) => $q->whereIn('almacen_id', $almacenesIds))
            ->where('estado', 'pendiente')
            ->orderBy('fecha_reporte', 'desc')
            ->take(5)
            ->get();

        // Verificar conexión con Planta
        $plantaConectada = $this->plantaApi->ping();

        return view('envios-planta.dashboard', compact(
            'enviosEnTransito',
            'enviosPendientes',
            'ultimosEntregados',
            'incidentesPendientes',
            'plantaConectada'
        ));
    }

    /**
     * Obtener IDs de almacenes del usuario actual
     */
    protected function getAlmacenesDelUsuario($usuario): array
    {
        if ($usuario->hasRole('propietario')) {
            return Almacen::where('user_id', $usuario->id)->pluck('id')->toArray();
        }

        if ($usuario->hasRole('administrador')) {
            $propietarioId = $usuario->user_id;
            return Almacen::where('user_id', $propietarioId)->pluck('id')->toArray();
        }

        // Para otros roles, buscar en tabla pivote almacen_user
        return $usuario->almacenes()->pluck('almacens.id')->toArray();
    }
}

