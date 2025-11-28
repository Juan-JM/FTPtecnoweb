<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Venta;
use Inertia\Inertia;
use App\Models\User;

class VentaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_venta' => 'required|date',
            'total' => 'required|numeric',
            'estado' => 'required|string|max:30',
            'usuario_id' => 'required|exists:users,id'
        ]);

        $venta = Venta::create($validated);

        return redirect()->route('admin.ventas')->with('success', 'Venta creada correctamente.');
    }

    public function update(Request $request, $id)
    {

        $venta = Venta::findOrFail($id);

        $total = $venta->detalleventas->sum(function ($detalle) {
            return $detalle->cantidad * $detalle->precio_unitario;
        });

        $venta->update([
            'total' => $total,
            'fecha_venta' => $request->input('fecha_venta', $venta->fecha_venta), // Si envías la fecha en el request
            'estado' => $request->input('estado', $venta->estado), // Si también cambias el estado
        ]);

        return redirect()->route('admin.ventas')->with('success', 'Venta actualizada correctamente.');
    }

    public function destroy($id)
    {
        $venta = Venta::findOrFail($id);
        $venta->delete();

        return redirect()->route('admin.ventas')->with('success', 'Venta eliminada correctamente.');
    }

    public function index()
    {
        $ventas = Venta::all();
        return Inertia::render('Admin/Ventas', [
            'ventas' => $ventas,
            'usuarios' => User::all(),
        ]);
    }

    public function crearVentaCliente(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'total' => 'required|numeric',
            'estado' => 'required|string',
        ]);

        try {
            $venta = new Venta();
            $venta->usuario_id = $request->usuario_id;
            $venta->total = $request->total;
            $venta->estado = $request->estado;
            $venta->fecha_venta = now();
            $venta->save();

            // Retornar la respuesta
            return response()->json($venta, 201);
        } catch (\Exception $e) {
            // Si hay un error, devolver el mensaje de error
            return response()->json(['error' => 'Error al crear la venta', 'message' => $e->getMessage()], 500);
        }
    }
}
