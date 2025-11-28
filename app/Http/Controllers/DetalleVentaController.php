<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Producto;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DetalleVentaController extends Controller
{
    public function store(Request $request, $ventaId)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $detalleVenta = new DetalleVenta([
            'producto_id' => $validated['producto_id'],
            'cantidad' => $validated['cantidad'],
            'precio_unitario' => $validated['precio_unitario'],
            'venta_id' => $ventaId, // solo usamos el id de la venta
        ]);

        $detalleVenta->save();

        // Retornar la respuesta
        return response()->json([
            'nuevoDetalle' => $detalleVenta,
        ]);
    }

    public function show($id)
    {
        $venta = Venta::find($id);  // Encuentra la venta por ID
        $detalleVentas = $venta->detalleventas;
        $productosDisponibles = Producto::all();

        return Inertia::render('Admin/Detalleventas', [
            'venta' => $venta,
            'detallesVenta' => $detalleVentas,
            'productosDisponibles' => $productosDisponibles,
        ]);
    }

    public function mostrarDetalleVentaCliente($id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json(['error' => 'Venta no encontrada'], 404);
        }

        $detalleVentas = $venta->detalleventas;
        $productos = Producto::all();
        return Inertia::render('DetalleVenta', [
            'venta' => $venta,
            'detallesVenta' => $detalleVentas,
            'productos' => $productos
        ]);
    }


    public function agregarProductoVentaCliente(Request $request, $ventaId)
    {
        $venta = Venta::find($ventaId);

        $producto = Producto::find($request->producto_id);

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $detalleVenta = new DetalleVenta();
        $detalleVenta->venta_id = $ventaId;
        $detalleVenta->producto_id = $producto->id;
        $detalleVenta->cantidad = $request->cantidad;
        $detalleVenta->precio_unitario = $producto->precio;
        $detalleVenta->save();

        $venta->total += $producto->precio * $request->cantidad;
        $venta->save();

        return response()->json([
            'message' => 'Producto agregado a la venta',
            'detalle' => $detalleVenta,
            'total' => $venta->total
        ]);
    }
}
