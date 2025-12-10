<?php

namespace App\Services;

use App\Models\EnvioPlanta;
use App\Models\EnvioPlantaProducto;
use App\Models\IncidentePlanta;
use App\Models\Almacen;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlantaApiService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.planta.url', 'http://127.0.0.1:8000'), '/');
        $this->timeout = config('services.planta.timeout', 30);
    }

    /**
     * Hacer una petición GET a la API de Planta
     */
    protected function get(string $endpoint, array $params = []): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/{$endpoint}", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("PlantaAPI GET Error: {$endpoint}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("PlantaAPI Exception: {$e->getMessage()}", [
                'endpoint' => $endpoint
            ]);
            return null;
        }
    }

    /**
     * Hacer una petición POST a la API de Planta
     */
    protected function post(string $endpoint, array $data = []): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/{$endpoint}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("PlantaAPI POST Error: {$endpoint}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("PlantaAPI Exception: {$e->getMessage()}", [
                'endpoint' => $endpoint,
                'data' => $data
            ]);
            return null;
        }
    }

    /**
     * Obtener todos los envíos destinados a un almacén específico
     */
    public function getEnviosParaAlmacen(string $almacenNombre): ?array
    {
        return $this->get('envios', ['almacen' => $almacenNombre]);
    }

    /**
     * Obtener detalle de un envío específico
     */
    public function getEnvio(int $envioId): ?array
    {
        return $this->get("envios/{$envioId}");
    }

    /**
     * Obtener envíos activos (en tránsito o esperando)
     */
    public function getEnviosActivos(): ?array
    {
        return $this->get('rutas/envios-activos');
    }

    /**
     * Obtener seguimiento de un envío (puntos GPS)
     */
    public function getSeguimiento(int $envioId): ?array
    {
        return $this->get("rutas/seguimiento/{$envioId}");
    }

    /**
     * Obtener documento/comprobante de un envío
     */
    public function getDocumentoUrl(int $envioId): string
    {
        return "{$this->baseUrl}/api/envios/{$envioId}/documento";
    }

    /**
     * Sincronizar envíos desde Planta para un almacén local
     */
    public function sincronizarEnvios(Almacen $almacen): array
    {
        $resultado = [
            'nuevos' => 0,
            'actualizados' => 0,
            'errores' => 0,
        ];

        // Obtener envíos activos desde Planta
        $enviosActivos = $this->getEnviosActivos();

        if (!$enviosActivos || !isset($enviosActivos['success'])) {
            Log::warning('No se pudieron obtener envíos activos de Planta');
            return $resultado;
        }

        // Procesar envíos en tránsito
        $todosEnvios = array_merge(
            $enviosActivos['en_transito'] ?? [],
            $enviosActivos['esperando'] ?? []
        );

        foreach ($todosEnvios as $envioData) {
            try {
                // Verificar si el envío es para este almacén (por nombre)
                if (stripos($envioData['almacen_nombre'] ?? '', $almacen->nombre) === false) {
                    continue;
                }

                // Obtener detalle completo del envío
                $detalle = $this->getEnvio($envioData['id']);
                
                if (!$detalle) {
                    $resultado['errores']++;
                    continue;
                }

                // Crear o actualizar el envío local
                $envioLocal = $this->crearOActualizarEnvio($detalle, $almacen);
                
                if ($envioLocal->wasRecentlyCreated) {
                    $resultado['nuevos']++;
                } else {
                    $resultado['actualizados']++;
                }

            } catch (\Exception $e) {
                Log::error("Error sincronizando envío: {$e->getMessage()}");
                $resultado['errores']++;
            }
        }

        return $resultado;
    }

    /**
     * Crear o actualizar un envío local basado en datos de Planta
     */
    public function crearOActualizarEnvio(array $data, Almacen $almacen): EnvioPlanta
    {
        $envio = EnvioPlanta::updateOrCreate(
            ['envio_planta_id' => $data['id']],
            [
                'codigo' => $data['codigo'],
                'almacen_id' => $almacen->id,
                'estado' => $data['estado'],
                'fecha_creacion' => $data['fecha_creacion'] ?? null,
                'fecha_estimada_entrega' => $data['fecha_estimada_entrega'] ?? null,
                'hora_estimada' => $data['hora_estimada'] ?? null,
                'fecha_asignacion' => $data['fecha_asignacion'] ?? null,
                'fecha_inicio_transito' => $data['fecha_inicio_transito'] ?? null,
                'fecha_entrega' => $data['fecha_entrega'] ?? null,
                'origen_lat' => $data['origen_lat'] ?? null,
                'origen_lng' => $data['origen_lng'] ?? null,
                'origen_direccion' => $data['origen_direccion'] ?? null,
                'destino_lat' => $data['latitud'] ?? null,
                'destino_lng' => $data['longitud'] ?? null,
                'destino_direccion' => $data['direccion_completa'] ?? null,
                'total_cantidad' => $data['total_cantidad'] ?? 0,
                'total_peso' => $data['total_peso'] ?? 0,
                'total_precio' => $data['total_precio'] ?? 0,
                'observaciones' => $data['observaciones'] ?? null,
                'sincronizado_at' => now(),
            ]
        );

        // Sincronizar productos
        if (isset($data['productos']) && is_array($data['productos'])) {
            $envio->productos()->delete();
            
            foreach ($data['productos'] as $prod) {
                EnvioPlantaProducto::create([
                    'envio_planta_id' => $envio->id,
                    'producto_nombre' => $prod['producto_nombre'],
                    'cantidad' => $prod['cantidad'] ?? 0,
                    'peso_unitario' => $prod['peso_unitario'] ?? 0,
                    'precio_unitario' => $prod['precio_unitario'] ?? 0,
                    'total_peso' => $prod['total_peso'] ?? 0,
                    'total_precio' => $prod['total_precio'] ?? 0,
                ]);
            }
        }

        return $envio;
    }

    /**
     * Actualizar ubicación de un envío en tránsito
     */
    public function actualizarUbicacion(EnvioPlanta $envio): bool
    {
        $seguimiento = $this->getSeguimiento($envio->envio_planta_id);

        if (!$seguimiento || !isset($seguimiento['data']) || empty($seguimiento['data'])) {
            return false;
        }

        // Obtener última ubicación
        $ultimaPosicion = end($seguimiento['data']);

        $envio->update([
            'ubicacion_lat' => $ultimaPosicion['latitud'] ?? null,
            'ubicacion_lng' => $ultimaPosicion['longitud'] ?? null,
            'ubicacion_actualizada_at' => now(),
        ]);

        return true;
    }

    /**
     * Sincronizar incidentes de un envío
     */
    public function sincronizarIncidentes(EnvioPlanta $envio, array $incidentes): int
    {
        $nuevos = 0;

        foreach ($incidentes as $incidenteData) {
            $incidente = IncidentePlanta::updateOrCreate(
                ['incidente_planta_id' => $incidenteData['id']],
                [
                    'envio_planta_id' => $envio->id,
                    'tipo_incidente' => $incidenteData['tipo_incidente'],
                    'descripcion' => $incidenteData['descripcion'] ?? null,
                    'foto_url' => $incidenteData['foto_url'] ?? null,
                    'estado' => $incidenteData['estado'],
                    'fecha_reporte' => $incidenteData['fecha_reporte'] ?? null,
                    'fecha_resolucion' => $incidenteData['fecha_resolucion'] ?? null,
                    'notas_resolucion' => $incidenteData['notas_resolucion'] ?? null,
                ]
            );

            if ($incidente->wasRecentlyCreated) {
                $nuevos++;
            }
        }

        return $nuevos;
    }

    /**
     * Crear un pedido en PlantaCruds
     * Envía los datos del pedido para que Planta cree el envío correspondiente
     */
    public function crearPedido(array $data): ?array
    {
        try {
            // Timeout corto para no bloquear la UI
            $response = Http::timeout(5)
                ->connectTimeout(3)
                ->post("{$this->baseUrl}/api/pedido-almacen", $data);

            if ($response->successful()) {
                $result = $response->json();
                Log::info("Pedido creado en Planta", ['response' => $result]);
                return $result;
            }

            Log::error("Error creando pedido en Planta", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear pedido: ' . $response->body()
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning("Planta no disponible: {$e->getMessage()}");
            return [
                'success' => false,
                'message' => 'Planta no está disponible en este momento'
            ];
        } catch (\Exception $e) {
            Log::error("Exception creando pedido en Planta: {$e->getMessage()}");
            return [
                'success' => false,
                'message' => 'Error de conexión con Planta'
            ];
        }
    }

    /**
     * Verificar si la API de Planta está disponible
     */
    public function ping(): bool
    {
        try {
            $response = Http::timeout(2)
                ->connectTimeout(2)
                ->get("{$this->baseUrl}/api/ping");
            
            return $response->successful() && 
                   isset($response->json()['success']) && 
                   $response->json()['success'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener la URL base de la API
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}

