<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EstadisticasController extends Controller
{

    public function index()
    {
        $ventasTotales = Venta::sum('total');

        $ventasPorMes = DB::table('ventas')
            ->select(DB::raw('EXTRACT(MONTH FROM fecha_venta) AS mes, SUM(total) AS total_vendido'))
            ->groupBy(DB::raw('EXTRACT(MONTH FROM fecha_venta)'))
            ->orderBy(DB::raw('EXTRACT(MONTH FROM fecha_venta)'))
            ->get();

        $ventasPorProducto = Producto::select('productos.id', 'productos.nombre', DB::raw('SUM(detalle_venta.cantidad) as cantidad_vendida'))
            ->join('detalle_venta', 'productos.id', '=', 'detalle_venta.producto_id') // Relación con detalle_venta
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id') // Relación con ventas
            ->groupBy('productos.id', 'productos.nombre') // Agrupar por producto
            ->get();

        $ventasPorUsuario = User::withSum('ventas as total_vendido', 'total')
            ->get();

            return Inertia::render('Estadisticas', [
                'ventasTotales' => $ventasTotales,
                'ventasPorPeriodo' => $ventasPorMes,
                'ventasPorProducto' => $ventasPorProducto,
                'ventasPorUsuario' => $ventasPorUsuario,
            ]);
    }

    public function ventasTotales()
    {
        $ventasTotales = Venta::sum('total');
        return response()->json($ventasTotales);
    }

    public function ventasPorProducto()
    {
        $ventasPorProducto = Producto::withCount('detalleVentas as cantidad_vendida')
            ->get();
        return response()->json($ventasPorProducto);
    }

    public function clientesNuevos()
    {
        $clientesNuevos = User::where('created_at', '>=', now()->subMonth())->count();
        return response()->json($clientesNuevos);
    }

    public function ventasPorPeriodo(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $ventasPeriodo = Venta::whereBetween('created_at', [$fechaInicio, $fechaFin])->sum('total');
        return response()->json($ventasPeriodo);
    }
}
