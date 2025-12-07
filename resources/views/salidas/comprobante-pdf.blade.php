<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Salida N° {{ $salida->codigo_comprobante }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 25px;
        }

        /* CONTENEDORES GENERALES */
        .box {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 5px;
        }

        .title-box {
            background: #e8e8e8;
            padding: 4px 6px;
            font-weight: bold;
            border: 1px solid #000;
            border-bottom: none;
        }

        .content-box {
            border: 1px solid #000;
            padding: 10px;
            min-height: 90px;
        }

        /* HEADER */
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        /* DETALLE TABLA */
        table.detalle {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table.detalle th {
            background: #e8e8e8;
            font-weight: bold;
            border: 1px solid #000;
            padding: 6px;
        }

        table.detalle td {
            border: 1px solid #000;
            padding: 6px;
        }

        /* FIRMAS */
        .firmas {
            margin-top: 30px;
            width: 100%;
            text-align: center;
        }

        .firmas td {
            padding-top: 40px;
        }

        .firma-linea {
            border-top: 1px solid #000;
            width: 200px;
            margin: 0 auto;
            padding-top: 3px;
        }
    </style>
</head>

<body>

    <!-- ================= CABECERA ================== -->
    <table class="header-table">
        <tr>
            <td width="34%" style="text-align:center">
                <strong class="header-title">{{ strtoupper($empresa->company) }}</strong><br>
                <span>TELÉFONO: {{ $empresa->phone_number }}</span>
            </td>

            <td width="34%" class="header-title">
                COMPROBANTE DE SALIDA
            </td>

            <td width="33%">
                <strong>NRO COMPROBANTE:</strong> {{ $salida->codigo_comprobante }} <br>
                <strong>FECHA:</strong> {{ \Carbon\Carbon::parse($salida->fecha)->format('d/m/Y') }} <br>
                <br>
                <strong>ESTADO:</strong>
                @switch($salida->estado)
                    @case(\App\Models\Salida::EMITIDO)
                        EMITIDO
                    @break

                    @case(\App\Models\Salida::CONFIRMADO)
                        CONFIRMADO
                    @break

                    @case(\App\Models\Salida::TERMINADO)
                        TERMINADO
                    @break

                    @case(\App\Models\Salida::ANULADO)
                        ANULADO
                    @break

                    @default
                        SIN ESTADO
                @endswitch
            </td>
        </tr>
    </table>

    <!-- ========= INFORMACIÓN DE LA SALIDA =========== -->
    <div class="title-box">INFORMACIÓN DE LA SALIDA</div>
    <div class="content-box">
        <table>
            <tr>
                {{-- <td><strong>Fecha emisión:</strong> {{ $salida->fecha }}</td> --}}
                <td><strong>Vigencia:</strong> {{ $salida->fecha_min }} → {{ $salida->fecha_max }}</td>
                <td><strong>Vehículo:</strong>{{ $salida->vehiculo->placa_identificacion ?? '---' }}</td>
            </tr>
            <tr>
                <td><strong>Almacén:</strong> {{ $salida->almacen->nombre }}</td>
                <td><strong>Punto de Venta:</strong> {{ $puntoVentaNombre }}</td>
            </tr>
            <tr>
                <td><strong>Tipo de salida:</strong> {{ $salida->tipoSalida->nombre }}</td>
                <td><strong>Nota Venta ID:</strong> {{ $salida->nota_venta_id }}</td>
            </tr>
            <tr>
                <td><strong>Operador:</strong>{{ $salida->operador->full_name }}</td>
                <td><strong>Transportista:</strong>{{ $salida->transportista->full_name }}</td>
            </tr>
            <tr>

            </tr>
        </table>
    </div>

    <!-- =========== DETALLE DE LA SALIDA ============= -->
    <div class="title-box">DETALLE DE LA SALIDA</div>
    <div class="content-box">
        <table class="detalle">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Unidad</th>
                    <th style="text-align:right">Cantidad</th>
                    <th style="text-align:right">Precio</th>
                    <th style="text-align:right">Total</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($salida->detalles as $d)
                    <tr>
                        <td>{{ $d->producto->nombre }}</td>
                        <td>{{ $d->producto->unidadMedida->cod_unidad_medida ?? '-' }}</td>
                        <td style="text-align:right">{{ number_format($d->cant_salida, 2) }}</td>
                        <td style="text-align:right">{{ number_format($d->precio, 2) }}</td>
                        <td style="text-align:right">{{ number_format($d->cant_salida * $d->precio, 2) }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="4" style="text-align:right;font-weight:bold;">TOTAL:</td>
                    <td style="text-align:right;font-weight:bold;">{{ number_format($monto_total, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ================ FIRMAS ================== -->
    <table class="firmas">
        <tr>
            <td>
                <div class="firma-linea"></div>
                Firma del Administrador<br>
                {{ $salida->administrador->full_name ?? '-' }}
            </td>

            <td>
                <div class="firma-linea"></div>
                Firma del Propietario<br>
                {{ $empresa->full_name ?? '-' }}
            </td>
        </tr>
    </table>

</body>

</html>
