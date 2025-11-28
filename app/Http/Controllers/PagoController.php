<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Venta;
use Inertia\Inertia;

class PagoController extends Controller
{
    public function index()
    {
        $pagos = Pago::with('venta')->get();

        return Inertia::render('Admin/Pagos', [
            'pagos' => $pagos,
            'ventas' => Venta::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_pago' => 'required|date',
            'monto' => 'required|numeric',
            'metodo_pago' => 'required|string|max:50',
            'venta_id' => 'required|exists:ventas,id',
        ]);

        $pago = Pago::create($validated);

        return Inertia::render('Admin/Pagos', [
            'pagos' => Pago::all(),
            'ventas' => Venta::all()
        ]);
    }

    public function destroy($id)
    {
        Pago::destroy($id);

        return Inertia::render('Admin/Pagos', [
            'pagos' => Pago::all(),
            'ventas' => Venta::all()
        ]);
    }

    public function registrarPago($request)
    {
        $monto = $request->monto;
        $metodo_pago = $request->metodo_pago;
        $fecha_pago = date('Y-m-d H:i:s');
        $venta_id = $request->venta_id;

        $pago = new Pago($monto, $metodo_pago, $fecha_pago, $venta_id);

        if ($pago->guardar()) {
            return json_encode(['status' => 'success', 'message' => 'Pago registrado exitosamente']);
        } else {
            return json_encode(['status' => 'error', 'message' => 'Error al registrar el pago']);
        }
    }

    public function crear(Request $request)
    {
        $validated = $request->validate([
            'fecha_pago' => 'required|date',
            'monto' => 'required|numeric',
            'metodo_pago' => 'required|string|max:50',
            'venta_id' => 'required|exists:ventas,id',
        ]);

        Pago::create($validated);

        return response()->json(['message' => 'Pago registrado correctamente']);
    }
}
