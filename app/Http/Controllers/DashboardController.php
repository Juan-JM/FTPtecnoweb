<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Compra;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Dashboard para usuarios regulares (clientes)
        $productos = Producto::with(['categoria'])
            ->where('cantidad', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => $producto->precio,
                    'cantidad' => $producto->cantidad,
                    'imagen_url' => $producto->imagen_url,
                    'categoria' => $producto->categoria,
                    'tiene_stock' => $producto->tieneStock(),
                ];
            });

        // Estadísticas totales
        $estadisticas = [
            'total_productos' => Producto::count(),
            'productos_en_stock' => Producto::where('cantidad', '>', 0)->count(),
            'productos_sin_stock' => Producto::where('cantidad', '=', 0)->count(),
        ];

        return Inertia::render('Dashboard', [
            'productos' => $productos,
            'usuario' => Auth::user(),
            'estadisticas' => $estadisticas,
        ]);
    }

    public function proveedorIndex()
    {
        // Dashboard específico para proveedores
        $usuario = Auth::user();
        
        // Obtener todos los productos (para que el proveedor pueda ver qué productos puede comprar)
        $productos = Producto::with(['categoria'])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => $producto->precio,
                    'cantidad' => $producto->cantidad,
                    'imagen_url' => $producto->imagen_url,
                    'categoria' => $producto->categoria,
                    'tiene_stock' => $producto->tieneStock(),
                ];
            });

        // Estadísticas del proveedor
        $comprasDelProveedor = Compra::where('usuario_id', $usuario->id)->count();
        $totalComprado = Compra::where('usuario_id', $usuario->id)->sum('total');
        $ultimaCompra = Compra::where('usuario_id', $usuario->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return Inertia::render('Proveedor/Dashboard', [
            'productos' => $productos,
            'usuario' => $usuario,
            'estadisticas' => [
                'compras_realizadas' => $comprasDelProveedor,
                'total_comprado' => $totalComprado,
                'ultima_compra' => $ultimaCompra,
            ],
        ]);
    }
}