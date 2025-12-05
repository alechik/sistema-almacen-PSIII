<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<title>Ingreso N° {{ $ingreso->codigo_comprobante }}</title>

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
      COMPROBANTE DE INGRESO
    </td>

    <td width="33%">
      <strong>NRO COMPROBANTE:</strong> {{ $ingreso->codigo_comprobante }} <br>
      <strong>FECHA:</strong> {{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }} <br>
      <br>
      <strong>ESTADO:</strong>
      {{-- @switch($ingreso->estado) --}}
        {{ $ingreso->estado == 1 ? 'EMITIDO' : 'COMPLETADO' }}
          {{-- @case(\App\Models\Ingreso::PENDIENTE) PENDIENTE @break
          @case(\App\Models\Ingreso::COMPLETADO) COMPLETADO @break
          @default ANULADO --}}
      {{-- @endswitch --}}
    </td>
  </tr>
</table>

<!-- ========= INFORMACIÓN DEL INGRESO =========== -->
<div class="title-box">INFORMACIÓN DEL INGRESO</div>
<div class="content-box">
  <strong>Almacén:</strong> {{ $ingreso->almacen->nombre }} <br>
  <strong>Proveedor:</strong> {{ $proveedor_nombre }} <br>
  <strong>Operador:</strong> {{ $ingreso->operador->full_name ?? '-' }} <br>
  <strong>Transportista:</strong> {{ $ingreso->transportista->full_name ?? '-' }} <br>
  <strong>Vehículo:</strong> {{ $ingreso->vehiculo->placa_identificacion ?? '-' }} <br>
  <strong>Tipo de Ingreso:</strong> {{ $ingreso->tipoIngreso->nombre ?? '-' }} <br>
</div>

<!-- =========== DETALLE DEL INGRESO ============= -->
<div class="title-box">DETALLE DEL INGRESO</div>
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
      @foreach($ingreso->detalles as $d)
      <tr>
        <td>{{ $d->producto->nombre }}</td>
        <td>{{ $d->producto->unidadMedida->cod_unidad_medida ?? '-' }}</td>
        <td style="text-align:right">{{ $d->cant_ingreso }}</td>
        <td style="text-align:right">{{ number_format($d->precio,4) }}</td>
        <td style="text-align:right">{{ number_format($d->cant_ingreso * $d->precio,4) }}</td>
      </tr>
      @endforeach

      <tr>
        <td colspan="4" style="text-align:right;font-weight:bold;">TOTAL:</td>
        <td style="text-align:right;font-weight:bold;">{{ number_format($monto_total,4) }}</td>
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
      {{ $ingreso->administrador->full_name ?? '-' }}
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
