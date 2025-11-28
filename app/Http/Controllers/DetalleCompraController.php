<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DetalleCompraController extends Controller
{
    public function store(Request $request, $compraId)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $detalleCompra = new DetalleCompra([
            'producto_id' => $validated['producto_id'],
            'cantidad' => $validated['cantidad'],
            'precio_unitario' => $validated['precio_unitario'],
            'compra_id' => $compraId,
        ]);

        $detalleCompra->save();

        return response()->json([
            'nuevoDetalle' => $detalleCompra,
        ]);
    }

    public function show($id)
    {
        $compra = Compra::find($id);  // Encuentra la venta por ID
        $detalleCompras = $compra->detallecompras;
        $productosDisponibles = Producto::all();

        return Inertia::render('Admin/Detallecompras', [
            'compras' => $compra,
            'detallesCompra' => $detalleCompras,
            'productosDisponibles' => $productosDisponibles,
        ]);
    }
}
