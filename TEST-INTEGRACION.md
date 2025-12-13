# Script de Prueba de Integración

Este script prueba todo el flujo de integración entre los tres sistemas:
- **sistema-almacen-PSIII** (puerto 8002)
- **Trazabilidad** (puerto 8000)
- **plantaCruds** (puerto 8001)

## Uso

```bash
cd C:\Users\Personal\Desktop\almacenes\sistema-almacen-PSIII
php test-integracion.php
```

## Qué hace el script

El script ejecuta las siguientes pruebas en orden:

1. **Verifica conexiones**: Comprueba que los tres sistemas estén accesibles
2. **Obtiene productos**: Obtiene productos disponibles de Trazabilidad
3. **Verifica pedido**: Verifica el estado de un pedido en Almacenes
4. **Verifica en Trazabilidad**: Comprueba si el pedido llegó a Trazabilidad
5. **Verifica envíos**: Busca envíos relacionados en plantaCruds
6. **Verifica asignación**: Comprueba si el envío tiene transportista asignado
7. **Verifica notificación**: Verifica si se notificó a Almacenes sobre la asignación

## Requisitos

- PHP 7.4 o superior
- Extensión `curl` de PHP habilitada
- Los tres sistemas deben estar corriendo en los puertos configurados

## Configuración

Si tus sistemas están en puertos diferentes, edita las URLs en el script:

```php
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
```

## Interpretación de resultados

- ✓ **Verde**: Operación exitosa
- ✗ **Rojo**: Error encontrado
- ⚠ **Amarillo**: Advertencia (algo puede no estar bien)
- ℹ **Azul**: Información adicional

Al final del script verás un resumen con todos los errores encontrados y los resultados obtenidos.

## Solución de problemas

Si el script muestra errores:

1. **Error de conexión**: Verifica que los tres sistemas estén corriendo
2. **Error 404**: Verifica que las rutas API existan
3. **Error 422/500**: Revisa los logs de Laravel del sistema correspondiente
4. **No encuentra pedido**: Asegúrate de tener un pedido creado y enviado a Trazabilidad

## Notas

- El script usa un pedido existente (por defecto ID 12) para las pruebas
- Puedes ingresar un ID diferente cuando el script lo solicite
- El script no crea datos nuevos, solo verifica el estado actual

