<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Recepción - {{ $envio->codigo }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #28a745;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #28a745;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header .codigo {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .header .fecha {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background: #f8f9fa;
            padding: 8px 12px;
            font-weight: bold;
            border-left: 4px solid #28a745;
            margin-bottom: 10px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            padding: 5px 10px;
            background: #f8f9fa;
            font-weight: bold;
            width: 40%;
            border: 1px solid #dee2e6;
        }
        .info-value {
            display: table-cell;
            padding: 5px 10px;
            border: 1px solid #dee2e6;
        }
        table.productos {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.productos th {
            background: #28a745;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table.productos td {
            padding: 8px 10px;
            border-bottom: 1px solid #dee2e6;
        }
        table.productos tr:nth-child(even) {
            background: #f8f9fa;
        }
        table.productos .text-center {
            text-align: center;
        }
        table.productos .text-end {
            text-align: right;
        }
        .totales {
            margin-top: 15px;
            text-align: right;
        }
        .totales table {
            margin-left: auto;
            border-collapse: collapse;
        }
        .totales td {
            padding: 5px 15px;
            border: 1px solid #dee2e6;
        }
        .totales .total-final {
            background: #28a745;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .firma-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .firma-grid {
            display: table;
            width: 100%;
        }
        .firma-box {
            display: table-cell;
            width: 45%;
            text-align: center;
            padding: 20px;
        }
        .firma-linea {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        .estado-entregado {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <h1>NOTA DE RECEPCIÓN</h1>
            <div class="codigo">{{ $envio->codigo }}</div>
            <div class="fecha">Documento generado el {{ now()->format('d/m/Y H:i:s') }}</div>
        </div>

        <!-- Estado -->
        <div style="text-align: center; margin-bottom: 20px;">
            <span class="estado-entregado">✓ ENTREGADO</span>
        </div>

        <!-- Información General -->
        <div class="section">
            <div class="section-title">Información del Envío</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Código del Envío</div>
                    <div class="info-value">{{ $envio->codigo }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Almacén Destino</div>
                    <div class="info-value">{{ $envio->almacen->nombre ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Dirección de Entrega</div>
                    <div class="info-value">{{ $envio->destino_direccion ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Fecha de Entrega</div>
                    <div class="info-value">{{ $envio->fecha_entrega?->format('d/m/Y H:i:s') ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Información del Transporte -->
        <div class="section">
            <div class="section-title">Información del Transporte</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Transportista</div>
                    <div class="info-value">{{ $envio->transportista_nombre ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Vehículo</div>
                    <div class="info-value">{{ $envio->vehiculo_placa ?? 'N/A' }} {{ $envio->vehiculo_descripcion ? '- '.$envio->vehiculo_descripcion : '' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Inicio de Tránsito</div>
                    <div class="info-value">{{ $envio->fecha_inicio_transito?->format('d/m/Y H:i') ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Productos Recibidos -->
        <div class="section">
            <div class="section-title">Productos Recibidos</div>
            <table class="productos">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Peso (kg)</th>
                        <th class="text-end">Precio Unit.</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($envio->productos as $producto)
                    <tr>
                        <td>{{ $producto->producto_nombre }}</td>
                        <td class="text-center">{{ $producto->cantidad }}</td>
                        <td class="text-end">{{ number_format($producto->total_peso, 2) }}</td>
                        <td class="text-end">Bs. {{ number_format($producto->precio_unitario, 2) }}</td>
                        <td class="text-end">Bs. {{ number_format($producto->total_precio, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totales">
                <table>
                    <tr>
                        <td>Total Unidades:</td>
                        <td><strong>{{ $envio->total_cantidad }}</strong></td>
                    </tr>
                    <tr>
                        <td>Peso Total:</td>
                        <td><strong>{{ number_format($envio->total_peso, 2) }} kg</strong></td>
                    </tr>
                    <tr class="total-final">
                        <td>TOTAL:</td>
                        <td>Bs. {{ number_format($envio->total_precio, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($envio->observaciones)
        <div class="section">
            <div class="section-title">Observaciones</div>
            <p style="padding: 10px; background: #f8f9fa; border-radius: 5px;">
                {{ $envio->observaciones }}
            </p>
        </div>
        @endif

        <!-- Firmas -->
        <div class="firma-section">
            <div class="section-title">Firmas de Conformidad</div>
            <div class="firma-grid">
                <div class="firma-box">
                    <div class="firma-linea">
                        <strong>Entregado por</strong><br>
                        {{ $envio->transportista_nombre ?? 'Transportista' }}<br>
                        <small>Transportista</small>
                    </div>
                </div>
                <div class="firma-box">
                    <div class="firma-linea">
                        <strong>Recibido por</strong><br>
                        ________________________<br>
                        <small>Responsable del Almacén</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Este documento certifica la recepción de los productos detallados.</p>
            <p>Sistema de Almacenes - Generado automáticamente</p>
        </div>
    </div>
</body>
</html>

