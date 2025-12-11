<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte General de Ingresos</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 25px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        table.general {
            width: 100%;
            border-collapse: collapse;
        }

        table.general th {
            background: #e8e8e8;
            border: 1px solid #000;
            padding: 6px;
            font-weight: bold;
        }

        table.general td {
            border: 1px solid #000;
            padding: 6px;
        }
    </style>
</head>

<body>

    <!-- ================= CABECERA ================= -->
    <table class="header-table">
        <tr>
            <td width="34%" style="text-align:center">
                <strong class="header-title">{{ strtoupper($empresa->company) }}</strong><br>
                TEL: {{ $empresa->phone_number }}
            </td>

            <td width="33%" class="header-title">
                REPORTE GENERAL DE INGRESOS
            </td>

            <td width="33%" style="text-align:left">
                <strong>FECHA:</strong> {{ now()->format('d/m/Y') }}<br>
                <strong>GENERADO POR:</strong> {{ $empresa->full_name }}
            </td>
        </tr>
    </table>

    <!-- ================= TABLA GENERAL ================= -->
    <table class="general">
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Fecha</th>
                <th>Administrador</th>
                <th>Almacén</th>
                <th>Proveedor</th>
                <th style="text-align:right;">Cant. Productos</th>
                <th style="text-align:right;">Monto Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($ingresos as $i)
                @php
                    $proveedor = collect($proveedores)->firstWhere('id', $i->proveedor_id)['nombre'] ?? '—';
                @endphp

                <tr>
                    <td>{{ $i->codigo_comprobante }}</td>
                    <td>{{ \Carbon\Carbon::parse($i->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $i->administrador->full_name ?? '-' }}</td>
                    <td>{{ $i->almacen->nombre ?? '—' }}</td>
                    <td>{{ $proveedor }}</td>
                    <td style="text-align:right;">{{ number_format($i->cantidad_total, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($i->monto_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
