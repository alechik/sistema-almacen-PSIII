<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salida;
use App\Models\Ingreso;
use App\Models\Producto;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    private $puntos_ventas = [
        ['id' => 1, 'nombre' => 'Punto de Venta 1'],
        ['id' => 2, 'nombre' => 'Punto de Venta 2'],
        ['id' => 3, 'nombre' => 'Punto de Venta 3'],
    ];

    public function index()
    {
        return view('reportes.index');
    }

    public function salidas(Request $request)
    {
        $salidas = Salida::with(['almacen', 'operador', 'transportista'])
            ->orderBy('fecha', 'desc')
            ->get();

        return view('reportes.salidas', compact('salidas'));
    }

    // public function ingresos(Request $request)
    // {
    //     $ingresos = Ingreso::with(['almacen', 'operador', 'transportista'])
    //         ->orderBy('fecha', 'desc')
    //         ->get();

    //     return view('reportes.ingresos', compact('ingresos'));
    // }

    public function salidasPdf()
    {
        $salidas = Salida::with([
            'almacen',
            'administrador',
            'detalles'
        ])
            ->orderBy('id', 'desc')
            ->get();

        // Calcular total general de cada salida
        foreach ($salidas as $s) {
            $s->total = $s->detalles->sum(fn($d) => $d->cant_salida * $d->precio);
            $s->cantidad_total = $s->detalles->sum('cant_salida');
        }
        if (Auth::user()->hasRole('propietario')) {
            $empresa = Auth::user();
        } else {
            $empresa = Auth::user()->parent;
        }
        // dd($empresa);

        // $empresa = User::find(Auth::user()->user_id);
        $puntos_ventas = $this->puntos_ventas;

        $pdf = Pdf::loadView('reportes.salidas-general', [
            'salidas' => $salidas,
            'empresa' => $empresa,
            'puntos_ventas' => $puntos_ventas
        ])->setPaper('a4', 'landscape');

        // ================= PIE DE PÁGINA ==================
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();
        $w = $canvas->get_width();
        $h = $canvas->get_height();

        // Izquierda
        $canvas->page_text(
            25,
            $h - 35,
            "Generado por: " . ($empresa->full_name ?? '-') .
                "     |     Fecha: " . now()->format('d/m/Y'),
            null,
            9,
            [0, 0, 0]
        );

        // Derecha
        $canvas->page_text(
            $w - 120,
            $h - 35,
            "Página {PAGE_NUM} de {PAGE_COUNT}",
            null,
            9,
            [0, 0, 0]
        );

        return $pdf->stream("Reporte_Salidas.pdf");
    }


    // public function ingresosPdf()
    // {
    //     $ingresos = Ingreso::with(['almacen', 'operador', 'transportista'])->get();
    //     $pdf = Pdf::loadView('reportes.pdf.ingresos', compact('ingresos'));
    //     return $pdf->download('Reporte_Ingresos.pdf');
    // }
}
