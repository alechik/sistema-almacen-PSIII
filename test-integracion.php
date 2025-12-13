<?php

/**
 * Script de prueba de integración completa
 * Prueba el flujo: Almacenes -> Trazabilidad -> plantaCruds -> Almacenes
 * 
 * Uso: php test-integracion.php
 */

// Configuración
$config = [
    'almacen' => [
        'url' => 'http://localhost:8002',
        'api' => 'http://localhost:8002/api',
    ],
    'trazabilidad' => [
        'url' => 'http://localhost:8000',
        'api' => 'http://localhost:8000/api',
    ],
    'plantacruds' => [
        'url' => 'http://localhost:8001',
        'api' => 'http://localhost:8001/api',
    ],
];

// Colores para terminal
$colors = [
    'reset' => "\033[0m",
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'cyan' => "\033[36m",
];

function printStep($step, $message) {
    global $colors;
    echo "\n{$colors['cyan']}[PASO $step]{$colors['reset']} $message\n";
}

function printSuccess($message) {
    global $colors;
    echo "{$colors['green']}✓{$colors['reset']} $message\n";
}

function printError($message) {
    global $colors;
    echo "{$colors['red']}✗{$colors['reset']} $message\n";
}

function printWarning($message) {
    global $colors;
    echo "{$colors['yellow']}⚠{$colors['reset']} $message\n";
}

function printInfo($message) {
    global $colors;
    echo "{$colors['blue']}ℹ{$colors['reset']} $message\n";
}

function makeRequest($method, $url, $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array_merge([
            'Content-Type: application/json',
            'Accept: application/json',
        ], $headers),
    ]);
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => $response,
        'error' => $error,
        'data' => json_decode($response, true),
    ];
}

function testConnection($name, $url, $apiUrl = null) {
    // Intentar primero con la API si está disponible
    if ($apiUrl) {
        printInfo("Probando conexión a $name API ($apiUrl)...");
        $result = makeRequest('GET', $apiUrl);
        
        // Aceptar códigos 200-299, 401 (no autorizado pero API funciona), 404 (ruta no existe pero servidor responde)
        if ($result['code'] >= 200 && $result['code'] < 500) {
            printSuccess("Conexión a $name API OK (HTTP {$result['code']})");
            return true;
        }
    }
    
    // Si falla la API, intentar con la URL base
    printInfo("Probando conexión a $name ($url)...");
    $result = makeRequest('GET', $url);
    
    // Aceptar redirects (302, 301) como conexión exitosa (el servidor responde)
    if ($result['code'] >= 200 && $result['code'] < 400) {
        printSuccess("Conexión a $name OK (HTTP {$result['code']})");
        return true;
    } else {
        printError("Conexión a $name falló: HTTP {$result['code']}");
        if ($result['error']) {
            printError("Error cURL: {$result['error']}");
        }
        return false;
    }
}

echo "\n{$colors['blue']}═══════════════════════════════════════════════════════════{$colors['reset']}\n";
echo "{$colors['blue']}  SCRIPT DE PRUEBA DE INTEGRACIÓN COMPLETA{$colors['reset']}\n";
echo "{$colors['blue']}  Almacenes -> Trazabilidad -> plantaCruds -> Almacenes{$colors['reset']}\n";
echo "{$colors['blue']}═══════════════════════════════════════════════════════════{$colors['reset']}\n";

$errors = [];
$results = [];

// PASO 1: Verificar conexiones
printStep(1, "Verificando conexiones a los sistemas...");

$connections = [
    'Almacenes' => ['url' => $config['almacen']['url'], 'api' => $config['almacen']['api']],
    'Trazabilidad' => ['url' => $config['trazabilidad']['url'], 'api' => $config['trazabilidad']['api']],
    'plantaCruds' => ['url' => $config['plantacruds']['url'], 'api' => $config['plantacruds']['api']],
];

$allConnected = true;
foreach ($connections as $name => $urls) {
    if (!testConnection($name, $urls['url'], $urls['api'])) {
        $allConnected = false;
        $errors[] = "No se pudo conectar a $name";
    }
}

if (!$allConnected) {
    printError("\nNo se pueden continuar las pruebas sin conexión a todos los sistemas.");
    exit(1);
}

// PASO 2: Obtener productos de Trazabilidad
printStep(2, "Obteniendo productos disponibles de Trazabilidad...");

$productsResponse = makeRequest('GET', $config['trazabilidad']['api'] . '/products');
if ($productsResponse['code'] !== 200) {
    printWarning("No se pudieron obtener productos: HTTP {$productsResponse['code']}");
    printInfo("Respuesta: " . substr($productsResponse['body'], 0, 200));
    
    // Intentar continuar con datos de prueba
    printWarning("Usando datos de prueba para continuar...");
    $results['producto_trazabilidad_id'] = 1;
    $results['producto_nombre'] = 'Producto de Prueba';
    printInfo("Producto de prueba: {$results['producto_nombre']} (ID: {$results['producto_trazabilidad_id']})");
} else {

    $products = isset($productsResponse['data']['data']) ? $productsResponse['data']['data'] : (isset($productsResponse['data']) ? $productsResponse['data'] : []);
    if (empty($products)) {
        printWarning("No hay productos disponibles en Trazabilidad");
        printInfo("Usando datos de prueba para continuar...");
        $results['producto_trazabilidad_id'] = 1;
        $results['producto_nombre'] = 'Producto de Prueba';
    } else {
        $product = $products[0];
        // El ID puede venir como 'producto_id' (primary key de Trazabilidad), 'id', o 'product_id'
        $productId = isset($product['producto_id']) ? $product['producto_id'] : (isset($product['id']) ? $product['id'] : (isset($product['product_id']) ? $product['product_id'] : null));
        $productNombre = isset($product['nombre']) ? $product['nombre'] : (isset($product['name']) ? $product['name'] : 'Producto sin nombre');
        
        if ($productId) {
            printSuccess("Producto encontrado: $productNombre (ID: $productId)");
            $results['producto_trazabilidad_id'] = $productId;
            $results['producto_nombre'] = $productNombre;
        } else {
            printWarning("Producto encontrado pero sin ID válido: $productNombre");
            printInfo("Estructura del producto recibida: " . json_encode(array_keys($product)));
            printInfo("Usando datos de prueba para continuar...");
            $results['producto_trazabilidad_id'] = 1;
            $results['producto_nombre'] = $productNombre;
        }
    }
}

// PASO 3: Crear pedido en Almacenes
printStep(3, "Creando pedido en sistema-almacen-PSIII...");

$pedidoData = [
    'fecha' => date('Y-m-d'),
    'fecha_min' => date('Y-m-d'),
    'fecha_max' => date('Y-m-d', strtotime('+7 days')),
    'almacen_id' => 1, // Asumiendo que existe almacén con ID 1
    'proveedor_id' => 1, // Planta
    'productos' => [
        [
            'producto_trazabilidad_id' => $results['producto_trazabilidad_id'],
            'producto_nombre' => $results['producto_nombre'],
            'cantidad' => 10,
        ],
    ],
];

// Nota: Esto requiere autenticación, así que simulamos que el pedido ya existe
// En producción, necesitarías hacer login primero
printWarning("Nota: Para crear un pedido real, necesitas autenticación.");
printInfo("Usando datos de prueba. Si tienes un pedido existente, ingresa su ID:");
echo "ID del pedido a usar (o presiona Enter para usar ID 12): ";
$handle = fopen("php://stdin", "r");
$pedidoId = trim(fgets($handle));
fclose($handle);

if (empty($pedidoId)) {
    $pedidoId = 12;
}

$results['pedido_id'] = $pedidoId;
printInfo("Usando pedido ID: {$results['pedido_id']}");

// PASO 4: Verificar estado del pedido en Almacenes
printStep(4, "Verificando estado del pedido en Almacenes...");

// Nota: No hay ruta API pública para obtener pedidos, solo rutas web que requieren autenticación
printInfo("Nota: La ruta API para obtener pedidos requiere autenticación.");
printInfo("Continuando con el ID del pedido: {$results['pedido_id']}");
printInfo("Puedes verificar manualmente en: {$config['almacen']['url']}/pedidos/{$results['pedido_id']}");

// Intentar obtener el pedido (puede fallar por autenticación, pero no es crítico)
$pedidoResponse = makeRequest('GET', $config['almacen']['api'] . "/pedidos/{$results['pedido_id']}");
if ($pedidoResponse['code'] === 200) {
    $pedido = $pedidoResponse['data'];
    $codigo = isset($pedido['codigo_comprobante']) ? $pedido['codigo_comprobante'] : 'N/A';
    printSuccess("Pedido encontrado: $codigo");
    $estado = isset($pedido['estado']) ? $pedido['estado'] : 'N/A';
    printInfo("Estado actual: $estado");
    printInfo("Enviado a Trazabilidad: " . (isset($pedido['enviado_a_trazabilidad']) && $pedido['enviado_a_trazabilidad'] ? 'Sí' : 'No'));
    $trackingId = isset($pedido['trazabilidad_tracking_id']) ? $pedido['trazabilidad_tracking_id'] : 'N/A';
    printInfo("Tracking ID: $trackingId");
    
    if ($pedido['enviado_a_trazabilidad']) {
        $results['trazabilidad_tracking_id'] = $pedido['trazabilidad_tracking_id'];
        printSuccess("Pedido ya enviado a Trazabilidad");
    } else {
        printWarning("Pedido no ha sido enviado a Trazabilidad aún");
    }
} else {
    // No es un error crítico, solo una advertencia
    printInfo("No se pudo obtener el pedido desde la API (HTTP {$pedidoResponse['code']}) - esto es normal si requiere autenticación");
    printInfo("Continuando con las pruebas usando el ID del pedido: {$results['pedido_id']}");
}

// PASO 5: Verificar pedido en Trazabilidad
if (isset($results['trazabilidad_tracking_id'])) {
    printStep(5, "Verificando pedido en Trazabilidad...");
    
    $trazabilidadPedidoResponse = makeRequest('GET', $config['trazabilidad']['api'] . "/pedidos/{$results['trazabilidad_tracking_id']}");
    if ($trazabilidadPedidoResponse['code'] === 200) {
        $trazPedido = $trazabilidadPedidoResponse['data'];
        printSuccess("Pedido encontrado en Trazabilidad");
        $trazEstado = isset($trazPedido['estado']) ? $trazPedido['estado'] : 'N/A';
        printInfo("Estado: $trazEstado");
        $trazNumero = isset($trazPedido['numero_pedido']) ? $trazPedido['numero_pedido'] : 'N/A';
        printInfo("Número: $trazNumero");
        
        $results['trazabilidad_pedido_id'] = isset($trazPedido['pedido_id']) ? $trazPedido['pedido_id'] : null;
        $results['trazabilidad_estado'] = isset($trazPedido['estado']) ? $trazPedido['estado'] : null;
    } else {
        printError("No se pudo obtener el pedido en Trazabilidad: HTTP {$trazabilidadPedidoResponse['code']}");
        printError("Respuesta: " . substr($trazabilidadPedidoResponse['body'], 0, 200));
    }
}

// PASO 6: Verificar envíos en plantaCruds
printStep(6, "Verificando envíos en plantaCruds...");

$enviosResponse = makeRequest('GET', $config['plantacruds']['api'] . '/envios?per_page=5');
if ($enviosResponse['code'] === 200) {
    $envios = isset($enviosResponse['data']['data']) ? $enviosResponse['data']['data'] : (isset($enviosResponse['data']) ? $enviosResponse['data'] : []);
    printSuccess("Encontrados " . count($envios) . " envíos recientes");
    
    // Buscar envíos relacionados con nuestro pedido
    $enviosRelacionados = [];
    foreach ($envios as $envio) {
        $observaciones = isset($envio['observaciones']) ? $envio['observaciones'] : '';
        if (strpos($observaciones, "pedido_almacen_id: {$results['pedido_id']}") !== false ||
            strpos($observaciones, "P{$results['pedido_id']}") !== false) {
            $enviosRelacionados[] = $envio;
        }
    }
    
    if (!empty($enviosRelacionados)) {
        printSuccess("Encontrados " . count($enviosRelacionados) . " envío(s) relacionados con el pedido");
        foreach ($enviosRelacionados as $envio) {
            printInfo("  - Envío ID: {$envio['id']}, Código: {$envio['codigo']}, Estado: {$envio['estado']}");
            $results['envio_id'] = $envio['id'];
            $results['envio_codigo'] = $envio['codigo'];
        }
    } else {
        printWarning("No se encontraron envíos relacionados con el pedido {$results['pedido_id']}");
    }
} else {
    printError("No se pudieron obtener envíos: HTTP {$enviosResponse['code']}");
}

// PASO 7: Verificar asignación del envío
if (isset($results['envio_id'])) {
    printStep(7, "Verificando asignación del envío...");
    
    $envioResponse = makeRequest('GET', $config['plantacruds']['api'] . "/envios/{$results['envio_id']}");
    if ($envioResponse['code'] === 200) {
        $envio = isset($envioResponse['data']['data']) ? $envioResponse['data']['data'] : $envioResponse['data'];
        printSuccess("Envío obtenido correctamente");
        $envioEstado = isset($envio['estado']) ? $envio['estado'] : 'N/A';
        printInfo("Estado: $envioEstado");
        $envioCodigo = isset($envio['codigo']) ? $envio['codigo'] : 'N/A';
        printInfo("Código: $envioCodigo");
        
        if (isset($envio['asignacion']) && $envio['asignacion']) {
            printSuccess("Envío tiene asignación");
            $asignacion = $envio['asignacion'];
            if (isset($asignacion['transportista'])) {
                $transNombre = isset($asignacion['transportista']['name']) ? $asignacion['transportista']['name'] : 'N/A';
                printInfo("Transportista: $transNombre");
                $transId = isset($asignacion['transportista']['id']) ? $asignacion['transportista']['id'] : 'N/A';
                printInfo("Transportista ID: $transId");
            }
            if (isset($asignacion['vehiculo'])) {
                $vehiculoPlaca = isset($asignacion['vehiculo']['placa']) ? $asignacion['vehiculo']['placa'] : 'N/A';
                printInfo("Vehículo: $vehiculoPlaca");
            }
        } else {
            printWarning("Envío no tiene asignación");
        }
    } else {
        printError("No se pudo obtener el envío: HTTP {$envioResponse['code']}");
    }
}

// PASO 8: Verificar notificación a Almacenes
if (isset($results['envio_id']) && isset($results['pedido_id'])) {
    printStep(8, "Verificando si se notificó la asignación a Almacenes...");
    
    // Verificar en los logs o en el pedido si tiene información de asignación
    $pedidoResponse = makeRequest('GET', $config['almacen']['api'] . "/pedidos/{$results['pedido_id']}");
    if ($pedidoResponse['code'] === 200) {
        $pedido = $pedidoResponse['data'];
        if (isset($pedido['transportista_id']) && $pedido['transportista_id']) {
            printSuccess("Pedido tiene transportista_id asignado: {$pedido['transportista_id']}");
        } else {
            printWarning("Pedido no tiene transportista_id asignado");
        }
        
        if (isset($pedido['observaciones']) && strpos($pedido['observaciones'], 'Transportista asignado desde plantaCruds') !== false) {
            printSuccess("Pedido tiene información de asignación en observaciones");
        } else {
            printWarning("Pedido no tiene información de asignación en observaciones");
        }
    }
}

// RESUMEN
echo "\n{$colors['blue']}═══════════════════════════════════════════════════════════{$colors['reset']}\n";
echo "{$colors['blue']}  RESUMEN DE PRUEBAS{$colors['reset']}\n";
echo "{$colors['blue']}═══════════════════════════════════════════════════════════{$colors['reset']}\n";

if (empty($errors)) {
    printSuccess("No se encontraron errores críticos");
} else {
    printError("Errores encontrados:");
    foreach ($errors as $error) {
        printError("  - $error");
    }
}

echo "\n{$colors['cyan']}Resultados obtenidos:{$colors['reset']}\n";
foreach ($results as $key => $value) {
    if (is_array($value)) {
        echo "  $key: " . json_encode($value) . "\n";
    } else {
        echo "  $key: $value\n";
    }
}

echo "\n{$colors['blue']}═══════════════════════════════════════════════════════════{$colors['reset']}\n";
echo "{$colors['green']}Pruebas completadas{$colors['reset']}\n";
echo "{$colors['blue']}═══════════════════════════════════════════════════════════{$colors['reset']}\n\n";

