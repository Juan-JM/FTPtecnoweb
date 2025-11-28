<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventarioController extends Controller
{
    public function index()
    {
        $inventarios = Inventario::with('producto')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($inventario) {
                return [
                    'id' => $inventario->id,
                    'producto_id' => $inventario->producto_id,
                    'producto' => $inventario->producto,
                    'cantidad_disponible' => $inventario->cantidad_disponible,
                    'fecha_ultima_actualizacion' => $inventario->fecha_ultima_actualizacion,
                    'tipo_movimiento' => $inventario->tipo_movimiento,
                    'cantidad_movimiento' => $inventario->cantidad_movimiento,
                    'created_at' => $inventario->created_at->format('d/m/Y H:i'),
                ];
            });

        $productos = Producto::with('categoria')->get();

        return Inertia::render('Admin/Inventarios', [
            'inventarios' => $inventarios,
            'productos' => $productos,
        ]);
    }

     // Método específico para proveedores
    public function proveedorIndex()
    {
        // Los proveedores pueden ver todos los movimientos de inventario
        // pero solo pueden crear movimientos de entrada
        $inventarios = Inventario::with(['producto.categoria'])
            ->orderBy('fecha_ultima_actualizacion', 'desc')
            ->get();
        
        $productos = Producto::with('categoria')->get();
        
        return Inertia::render('Proveedor/Inventarios', [
            'inventarios' => $inventarios,
            'productos' => $productos,
            'usuario' => Auth::user(),
        ]);
    }

    // Crear movimiento de inventario para proveedores (solo entradas)
    public function proveedorStore(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad_movimiento' => 'required|integer|min:1',
            'tipo_movimiento' => 'required|in:entrada',
            'fecha_ultima_actualizacion' => 'required|date',
        ], [
            'producto_id.required' => 'Debe seleccionar un producto.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'cantidad_movimiento.required' => 'La cantidad es obligatoria.',
            'cantidad_movimiento.integer' => 'La cantidad debe ser un número entero.',
            'cantidad_movimiento.min' => 'La cantidad debe ser mayor a 0.',
            'tipo_movimiento.required' => 'El tipo de movimiento es obligatorio.',
            'tipo_movimiento.in' => 'Los proveedores solo pueden realizar movimientos de entrada.',
            'fecha_ultima_actualizacion.required' => 'La fecha es obligatoria.',
            'fecha_ultima_actualizacion.date' => 'La fecha debe ser válida.',
        ]);

        $producto = Producto::findOrFail($validated['producto_id']);
        
        // Actualizar cantidad del producto
        $producto->cantidad += $validated['cantidad_movimiento'];
        $producto->save();

        // Crear registro de inventario
        Inventario::create([
            'producto_id' => $validated['producto_id'],
            'cantidad_disponible' => $producto->cantidad,
            'fecha_ultima_actualizacion' => $validated['fecha_ultima_actualizacion'],
            'tipo_movimiento' => $validated['tipo_movimiento'],
            'cantidad_movimiento' => $validated['cantidad_movimiento'],
        ]);

        return redirect()->route('proveedor.inventarios')
            ->with('success', 'Movimiento de inventario registrado exitosamente.');
    }

    // Actualizar movimiento de inventario para proveedores
    public function proveedorUpdate(Request $request, $id)
    {
        $inventario = Inventario::findOrFail($id);
        
        // Los proveedores solo pueden modificar entradas recientes (últimas 24 horas)
        $horasTranscurridas = now()->diffInHours($inventario->created_at);
        if ($horasTranscurridas > 24) {
            return back()->withErrors(['error' => 'No se pueden modificar movimientos de más de 24 horas.']);
        }

        if ($inventario->tipo_movimiento !== 'entrada') {
            return back()->withErrors(['error' => 'Los proveedores solo pueden modificar movimientos de entrada.']);
        }

        $validated = $request->validate([
            'cantidad_movimiento' => 'required|integer|min:1',
            'fecha_ultima_actualizacion' => 'required|date',
        ], [
            'cantidad_movimiento.required' => 'La cantidad es obligatoria.',
            'cantidad_movimiento.integer' => 'La cantidad debe ser un número entero.',
            'cantidad_movimiento.min' => 'La cantidad debe ser mayor a 0.',
            'fecha_ultima_actualizacion.required' => 'La fecha es obligatoria.',
            'fecha_ultima_actualizacion.date' => 'La fecha debe ser válida.',
        ]);

        $producto = $inventario->producto;
        
        // Revertir el movimiento anterior
        $producto->cantidad -= $inventario->cantidad_movimiento;
        
        // Aplicar el nuevo movimiento
        $producto->cantidad += $validated['cantidad_movimiento'];
        $producto->save();

        // Actualizar el registro
        $inventario->update([
            'cantidad_disponible' => $producto->cantidad,
            'fecha_ultima_actualizacion' => $validated['fecha_ultima_actualizacion'],
            'cantidad_movimiento' => $validated['cantidad_movimiento'],
        ]);

        return redirect()->route('proveedor.inventarios')
            ->with('success', 'Movimiento de inventario actualizado exitosamente.');
    }

    // Eliminar movimiento de inventario para proveedores
    public function proveedorDestroy($id)
    {
        $inventario = Inventario::findOrFail($id);
        
        // Los proveedores solo pueden eliminar entradas recientes (últimas 24 horas)
        $horasTranscurridas = now()->diffInHours($inventario->created_at);
        if ($horasTranscurridas > 24) {
            return back()->withErrors(['error' => 'No se pueden eliminar movimientos de más de 24 horas.']);
        }

        if ($inventario->tipo_movimiento !== 'entrada') {
            return back()->withErrors(['error' => 'Los proveedores solo pueden eliminar movimientos de entrada.']);
        }

        $producto = $inventario->producto;
        
        // Verificar que hay suficiente stock para revertir
        if ($producto->cantidad >= $inventario->cantidad_movimiento) {
            $producto->cantidad -= $inventario->cantidad_movimiento;
            $producto->save();
            
            $inventario->delete();
            
            return redirect()->route('proveedor.inventarios')
                ->with('success', 'Movimiento de inventario eliminado exitosamente.');
        } else {
            return back()->withErrors(['error' => 'No se puede eliminar: no hay suficiente stock para revertir el movimiento.']);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo_movimiento' => 'required|in:entrada,salida,compra,venta,ajuste',
            'cantidad_movimiento' => 'required|integer|min:1',
        ], [
            'producto_id.required' => 'Debe seleccionar un producto.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'tipo_movimiento.required' => 'El tipo de movimiento es obligatorio.',
            'tipo_movimiento.in' => 'El tipo de movimiento debe ser: entrada, salida, compra, venta o ajuste.',
            'cantidad_movimiento.required' => 'La cantidad de movimiento es obligatoria.',
            'cantidad_movimiento.integer' => 'La cantidad debe ser un número entero.',
            'cantidad_movimiento.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        DB::beginTransaction();
        
        try {
            $producto = Producto::findOrFail($validated['producto_id']);
            $cantidadAnterior = $producto->cantidad;

            // Calcular la nueva cantidad según el tipo de movimiento
            switch ($validated['tipo_movimiento']) {
                case 'entrada':
                case 'compra':
                    $nuevaCantidad = $cantidadAnterior + $validated['cantidad_movimiento'];
                    break;
                
                case 'salida':
                case 'venta':
                    if ($cantidadAnterior < $validated['cantidad_movimiento']) {
                        throw new \Exception('No hay suficiente stock disponible. Stock actual: ' . $cantidadAnterior);
                    }
                    $nuevaCantidad = $cantidadAnterior - $validated['cantidad_movimiento'];
                    break;
                
                case 'ajuste':
                    // Para ajustes, la cantidad_movimiento es la nueva cantidad total
                    $nuevaCantidad = $validated['cantidad_movimiento'];
                    $validated['cantidad_movimiento'] = $nuevaCantidad - $cantidadAnterior; // Diferencia
                    break;
                
                default:
                    throw new \Exception('Tipo de movimiento no válido.');
            }

            // Actualizar el producto
            $producto->cantidad = $nuevaCantidad;
            $producto->save();

            // Crear el registro de inventario
            Inventario::create([
                'producto_id' => $validated['producto_id'],
                'cantidad_disponible' => $nuevaCantidad,
                'fecha_ultima_actualizacion' => now(),
                'tipo_movimiento' => $validated['tipo_movimiento'],
                'cantidad_movimiento' => abs($validated['cantidad_movimiento']),
            ]);

            DB::commit();

            return redirect()->route('admin.inventarios')
                ->with('success', 'Movimiento de inventario registrado exitosamente. Stock actualizado: ' . $nuevaCantidad);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $inventario = Inventario::findOrFail($id);

        $validated = $request->validate([
            'tipo_movimiento' => 'required|in:entrada,salida,compra,venta,ajuste',
            'cantidad_movimiento' => 'required|integer|min:1',
        ], [
            'tipo_movimiento.required' => 'El tipo de movimiento es obligatorio.',
            'tipo_movimiento.in' => 'El tipo de movimiento debe ser: entrada, salida, compra, venta o ajuste.',
            'cantidad_movimiento.required' => 'La cantidad de movimiento es obligatoria.',
            'cantidad_movimiento.integer' => 'La cantidad debe ser un número entero.',
            'cantidad_movimiento.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        DB::beginTransaction();
        
        try {
            $producto = $inventario->producto;
            
            // Revertir el movimiento anterior
            switch ($inventario->tipo_movimiento) {
                case 'entrada':
                case 'compra':
                    $producto->cantidad -= $inventario->cantidad_movimiento;
                    break;
                case 'salida':
                case 'venta':
                    $producto->cantidad += $inventario->cantidad_movimiento;
                    break;
            }

            // Aplicar el nuevo movimiento
            switch ($validated['tipo_movimiento']) {
                case 'entrada':
                case 'compra':
                    $nuevaCantidad = $producto->cantidad + $validated['cantidad_movimiento'];
                    break;
                case 'salida':
                case 'venta':
                    if ($producto->cantidad < $validated['cantidad_movimiento']) {
                        throw new \Exception('No hay suficiente stock disponible para este movimiento.');
                    }
                    $nuevaCantidad = $producto->cantidad - $validated['cantidad_movimiento'];
                    break;
                case 'ajuste':
                    $nuevaCantidad = $validated['cantidad_movimiento'];
                    break;
            }

            $producto->cantidad = $nuevaCantidad;
            $producto->save();

            // Actualizar el registro de inventario
            $inventario->update([
                'cantidad_disponible' => $nuevaCantidad,
                'fecha_ultima_actualizacion' => now(),
                'tipo_movimiento' => $validated['tipo_movimiento'],
                'cantidad_movimiento' => $validated['cantidad_movimiento'],
            ]);

            DB::commit();

            return redirect()->route('admin.inventarios')
                ->with('success', 'Movimiento de inventario actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $inventario = Inventario::findOrFail($id);

        DB::beginTransaction();
        
        try {
            $producto = $inventario->producto;

            // Revertir el movimiento
            switch ($inventario->tipo_movimiento) {
                case 'entrada':
                case 'compra':
                    if ($producto->cantidad >= $inventario->cantidad_movimiento) {
                        $producto->cantidad -= $inventario->cantidad_movimiento;
                    } else {
                        throw new \Exception('No se puede revertir: el stock actual es menor al movimiento registrado.');
                    }
                    break;
                case 'salida':
                case 'venta':
                    $producto->cantidad += $inventario->cantidad_movimiento;
                    break;
            }

            $producto->save();
            $inventario->delete();

            DB::commit();

            return redirect()->route('admin.inventarios')
                ->with('success', 'Movimiento eliminado y stock revertido exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Método para obtener resumen de inventario
    public function resumen()
    {
        $productos = Producto::with('categoria')
            ->select('id', 'nombre', 'cantidad', 'precio', 'categoria_id')
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'cantidad' => $producto->cantidad,
                    'precio' => $producto->precio,
                    'categoria' => $producto->categoria?->nombre,
                    'valor_total' => $producto->cantidad * $producto->precio,
                    'estado_stock' => $producto->cantidad > 10 ? 'Bueno' : ($producto->cantidad > 0 ? 'Bajo' : 'Agotado'),
                ];
            });

        return Inertia::render('Admin/ResumenInventario', [
            'productos' => $productos,
            'totales' => [
                'productos_total' => $productos->count(),
                'productos_en_stock' => $productos->where('cantidad', '>', 0)->count(),
                'productos_agotados' => $productos->where('cantidad', '=', 0)->count(),
                'valor_total_inventario' => $productos->sum('valor_total'),
            ],
        ]);
    }
}