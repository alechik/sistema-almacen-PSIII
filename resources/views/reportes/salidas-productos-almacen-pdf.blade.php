<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Salidas por Almacén</title>

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
            font-size: 15px;
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

        .text-right {
            text-align: right;
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
            REPORTE DE SALIDAS POR ALMACÉN Y PRODUCTO
        </td>

        <td width="33%">
            <strong>FECHA:</strong> {{ now()->format('d/m/Y') }}<br>
            <strong>GENERADO POR:</strong> {{ $empresa->full_name }}
        </td>
    </tr>
</table>

<!-- ================= TABLA ================= -->
<table class="general">
    <thead>
        <tr>
            <th>Almacén</th>
            <th>Producto</th>
            <th class="text-right">Cantidad Total</th>
            <th class="text-right">Costo Total</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($data as $r)
            <tr>
                <td>{{ $r->almacen }}</td>
                <td>{{ $r->producto }}</td>
                <td class="text-right">{{ number_format($r->cantidad_total, 2) }}</td>
                <td class="text-right">{{ number_format($r->costo_total, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align:center">No existen datos</td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
