<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CarritoController extends Controller
{
    public function index()
    {
        $carritoItems = Carrito::carritoUsuario(Auth::id());
        $total = Carrito::totalCarrito(Auth::id());
        
        return Inertia::render('Carrito', [
            'carritoItems' => $carritoItems,
            'total' => $total,
        ]);
    }

    public function agregar(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1|max:100'
        ], [
            'producto_id.required' => 'Debe seleccionar un producto.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'cantidad.max' => 'La cantidad no puede ser mayor a 100.',
        ]);

        $resultado = Carrito::agregarProducto(
            Auth::id(),
            $validated['producto_id'],
            $validated['cantidad']
        );

        if ($resultado['success']) {
            return response()->json([
                'success' => true,
                'message' => $resultado['message'],
                'carrito_count' => Carrito::contarItems(Auth::id())
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $resultado['message']
            ], 400);
        }
    }

    public function actualizar(Request $request, $id)
    {
        $validated = $request->validate([
            'cantidad' => 'required|integer|min:1|max:100'
        ], [
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'cantidad.max' => 'La cantidad no puede ser mayor a 100.',
        ]);

        $carritoItem = Carrito::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->firstOrFail();

        // Verificar stock
        if (!$carritoItem->producto->tieneStock($validated['cantidad'])) {
            return response()->json([
                'success' => false,
                'message' => 'No hay suficiente stock disponible'
            ], 400);
        }

        $carritoItem->cantidad = $validated['cantidad'];
        $carritoItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Cantidad actualizada exitosamente',
            'subtotal' => $carritoItem->subtotal,
            'total' => Carrito::totalCarrito(Auth::id())
        ]);
    }

    public function eliminar($id)
    {
        $carritoItem = Carrito::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->firstOrFail();

        $carritoItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado del carrito',
            'carrito_count' => Carrito::contarItems(Auth::id()),
            'total' => Carrito::totalCarrito(Auth::id())
        ]);
    }

    public function limpiar()
    {
        Carrito::where('usuario_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Carrito vaciado exitosamente'
        ]);
    }

    public function contarItems()
    {
        return response()->json([
            'count' => Carrito::contarItems(Auth::id())
        ]);
    }

    public function pago()
    {
        $carritoItems = Carrito::carritoUsuario(Auth::id());
        $total = Carrito::totalCarrito(Auth::id());
        $usuario = Auth::user();
        
        // Calcular descuentos
        $descuentos = $carritoItems->reduce(function ($totalDesc, $item) {
            $descuentoProducto = $item->producto->descuento ?? 0;
            $subtotalProducto = $item->cantidad * $item->precio_unitario;
            return $totalDesc + ($subtotalProducto * $descuentoProducto / 100);
        }, 0);
        
        $subtotal = $total;
        $totalFinal = $subtotal - $descuentos;
        
        return Inertia::render('PagoFacil', [
            'carritoItems' => $carritoItems,
            'subtotal' => $subtotal,
            'descuentos' => $descuentos,
            'total' => $totalFinal,
            'usuario' => $usuario
        ]);
    }
}