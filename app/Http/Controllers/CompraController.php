<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\User;
use App\Models\Inventario;
use App\Models\DetalleCompra;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompraController extends Controller
{
    // Método para administradores
    public function index()
    {
        $compras = Compra::with(['usuario', 'detalles.producto'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $usuarios = User::all();
        
        return Inertia::render('Admin/Compras', [
            'compras' => $compras,
            'usuarios' => $usuarios,
        ]);
    }

    // Método específico para proveedores
    public function proveedorIndex()
    {
        $compras = Compra::with(['usuario', 'detalles.producto'])
            ->where('usuario_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        $productos = Producto::with('categoria')->get();
        
        return Inertia::render('Proveedor/Compras', [
            'compras' => $compras,
            'productos' => $productos,
            'usuario' => Auth::user(),
        ]);
    }

    // Crear compra para proveedores
    public function proveedorStore(Request $request)
    {
        $validated = $request->validate([
            'fecha_compra' => 'required|date',
            'total' => 'required|numeric|min:0',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ], [
            'fecha_compra.required' => 'La fecha de compra es obligatoria.',
            'fecha_compra.date' => 'La fecha debe ser válida.',
            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser un número.',
            'total.min' => 'El total debe ser mayor o igual a 0.',
            'detalles.required' => 'Debe agregar al menos un producto.',
            'detalles.array' => 'Los detalles deben ser un array.',
            'detalles.min' => 'Debe agregar al menos un producto.',
            'detalles.*.producto_id.required' => 'Debe seleccionar un producto.',
            'detalles.*.producto_id.exists' => 'El producto seleccionado no existe.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria.',
            'detalles.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
            'detalles.*.precio_unitario.numeric' => 'El precio unitario debe ser un número.',
            'detalles.*.precio_unitario.min' => 'El precio unitario debe ser mayor o igual a 0.',
        ]);

        DB::beginTransaction();
        
        try {
            // Crear la compra
            $compra = Compra::create([
                'usuario_id' => Auth::id(),
                'fecha_compra' => $validated['fecha_compra'],
                'total' => $validated['total'],
            ]);

            // Procesar cada detalle de compra
            foreach ($validated['detalles'] as $detalle) {
                // Crear detalle de compra
                DetalleCompra::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                ]);

                // Actualizar inventario del producto
                $producto = Producto::findOrFail($detalle['producto_id']);
                $producto->cantidad += $detalle['cantidad'];
                $producto->save();

                // Crear movimiento de inventario
                Inventario::create([
                    'producto_id' => $detalle['producto_id'],
                    'cantidad_disponible' => $producto->cantidad,
                    'fecha_ultima_actualizacion' => now(),
                    'tipo_movimiento' => 'entrada',
                    'cantidad_movimiento' => $detalle['cantidad'],
                ]);
            }

            DB::commit();

            return redirect()->route('proveedor.compras')
                ->with('success', 'Compra registrada exitosamente y inventario actualizado.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al procesar la compra: ' . $e->getMessage()]);
        }
    }

    // Actualizar compra para proveedores
    public function proveedorUpdate(Request $request, $id)
    {
        $compra = Compra::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'fecha_compra' => 'required|date',
            'total' => 'required|numeric|min:0',
        ], [
            'fecha_compra.required' => 'La fecha de compra es obligatoria.',
            'fecha_compra.date' => 'La fecha debe ser válida.',
            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser un número.',
            'total.min' => 'El total debe ser mayor o igual a 0.',
        ]);

        $compra->update($validated);

        return redirect()->route('proveedor.compras')
            ->with('success', 'Compra actualizada exitosamente.');
    }

    // Eliminar compra para proveedores
    public function proveedorDestroy($id)
    {
        $compra = Compra::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->with('detalles')
            ->firstOrFail();

        DB::beginTransaction();
        
        try {
            // Revertir movimientos de inventario
            foreach ($compra->detalles as $detalle) {
                $producto = Producto::findOrFail($detalle->producto_id);
                
                // Verificar que hay suficiente stock para revertir
                if ($producto->cantidad >= $detalle->cantidad) {
                    $producto->cantidad -= $detalle->cantidad;
                    $producto->save();

                    // Crear movimiento de salida en inventario
                    Inventario::create([
                        'producto_id' => $detalle->producto_id,
                        'cantidad_disponible' => $producto->cantidad,
                        'fecha_ultima_actualizacion' => now(),
                        'tipo_movimiento' => 'salida',
                        'cantidad_movimiento' => $detalle->cantidad,
                    ]);
                }
            }

            $compra->delete();
            
            DB::commit();

            return redirect()->route('proveedor.compras')
                ->with('success', 'Compra eliminada exitosamente e inventario revertido.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al eliminar la compra: ' . $e->getMessage()]);
        }
    }

    // Métodos para administradores (mantener los existentes)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'fecha_compra' => 'required|date',
            'total' => 'required|numeric|min:0',
        ], [
            'usuario_id.required' => 'Debe seleccionar un usuario.',
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
            'fecha_compra.required' => 'La fecha de compra es obligatoria.',
            'fecha_compra.date' => 'La fecha debe ser válida.',
            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser un número.',
            'total.min' => 'El total debe ser mayor o igual a 0.',
        ]);

        Compra::create($validated);

        return redirect()->route('admin.compras')
            ->with('success', 'Compra creada exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $compra = Compra::findOrFail($id);

        $validated = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'fecha_compra' => 'required|date',
            'total' => 'required|numeric|min:0',
        ], [
            'usuario_id.required' => 'Debe seleccionar un usuario.',
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
            'fecha_compra.required' => 'La fecha de compra es obligatoria.',
            'fecha_compra.date' => 'La fecha debe ser válida.',
            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser un número.',
            'total.min' => 'El total debe ser mayor o igual a 0.',
        ]);

        $compra->update($validated);

        return redirect()->route('admin.compras')
            ->with('success', 'Compra actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $compra = Compra::findOrFail($id);
        $compra->delete();

        return redirect()->route('admin.compras')
            ->with('success', 'Compra eliminada exitosamente.');
    }
}