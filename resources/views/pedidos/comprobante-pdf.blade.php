<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<title>Pedido N° {{ $pedido->codigo_comprobante }}</title>

<style>
  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #000;
    margin: 25px;
  }

  /* CONTENEDOR PRINCIPAL */
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

  /* FOOTER */
  @page { margin: 25px 25px 80px 25px; }
  #footer {
      position: fixed;
      bottom: 0px;
      left: 0;
      right: 0;
      height: 40px;
      padding: 5px 25px;
      font-size: 9px;
      color: #444;
  }

  /* forzar bloque y permitir que DOMPDF renderice los contadores */
    /* #footer .page-number {
        float: right;
    }

    #footer .page-number:after {
        content: "Página " counter(page) " de " counter(pages);
    } */

</style>
</head>

<body>

<!-- ================= CABECERA ================== -->
<table class="header-table">
  <tr>
    <td width="34%" style="text-align:center">
      <strong class="header-title" >{{ strtoupper($empresa->company) }}</strong><br>
      <spam>TELÉFONO:{{$empresa->phone_number}}</spam>
    </td>

    <td width="34%" class="header-title">
      COMPROBANTE DE PEDIDO
    </td>

    <td width="33%">
      <strong>NRO COMPROBANTE:</strong> {{ $pedido->codigo_comprobante }} <br>
      <strong>FECHA:</strong> {{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }} <br>
      <br>
      <strong>ESTADO:</strong>
      @switch($pedido->estado)
          @case(\App\Models\Pedido::EMITIDO) EMITIDO @break
          @case(\App\Models\Pedido::CONFIRMADO) CONFIRMADO @break
          @case(\App\Models\Pedido::TERMINADO) TERMINADO @break
          @case(\App\Models\Pedido::CANCELADO) CANCELADO @break
          @default ANULADO
      @endswitch
    </td>
  </tr>
</table>


<!-- ========= INFORMACIÓN DEL PEDIDO =========== -->
<div class="title-box">INFORMACIÓN DEL PEDIDO</div>
<div class="content-box">
  <strong>Almacén:</strong> {{ $pedido->almacen->nombre }} <br>
  <strong>Proveedor:</strong> {{ collect($proveedores)->firstWhere('id',$pedido->proveedor_id)['nombre'] ?? 'No definido' }} <br>
  <strong>Operador:</strong> {{ $pedido->operador->full_name ?? '-' }} <br>
  <strong>Transportista:</strong> {{ $pedido->transportista->full_name ?? '-' }} <br>
</div>


<!-- =========== DETALLE DEL PEDIDO ============= -->
<div class="title-box">DETALLE DEL PEDIDO</div>
<div class="content-box">

  <table class="detalle">
    <thead>
      <tr>
        <th>Producto</th>
        <th width="80px" style="text-align:center">Cantidad</th>
      </tr>
    </thead>
    <tbody>
      @foreach($pedido->detalles as $d)
      <tr>
        <td>{{ $d->producto->nombre }}</td>
        <td style="text-align:center">{{ $d->cantidad }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

</div>


<!-- ================ FIRMAS ================== -->
<table class="firmas">
  <tr>
    <td>
      <div class="firma-linea"></div>
      Firma del Administrador<br>
      {{ $pedido->administrador->full_name ?? '-' }}
    </td>

    <td>
      <div class="firma-linea"></div>
      Firma del Propietario<br>
      {{ $empresa->full_name ?? '-' }}
    </td>
  </tr>
</table>

<!-- ================= FOOTER ================= -->

</body>
</html>
