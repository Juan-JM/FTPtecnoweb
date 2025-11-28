<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promocion;
use App\Models\Producto;
use Inertia\Inertia;

class PromocionController extends Controller
{
    public function index()
    {
        $promociones = Promocion::with('producto')->get();

        return Inertia::render('Admin/Promocion', [
            'promociones' => $promociones,
            'productos' => Producto::all(),  // Obtener productos para mostrar en la vista de creación
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'required|string|max:100',
            'descuento_porcentaje' => 'required|integer|between:0,100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'producto_id' => 'required|exists:productos,id',
        ]);

        Promocion::create($request->all());

        return Inertia::render('Admin/Promocion', [
            'promociones' => Promocion::with('producto')->get(),
            'productos' => Producto::all(),
        ]);
    }

    public function edit(Promocion $promocion)
    {
        return Inertia::render('Admin/Promocion', [
            'promocion' => $promocion,
            'productos' => Producto::all(),
        ]);
    }

    public function update(Request $request, Promocion $promocion)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'required|string|max:100',
            'descuento_porcentaje' => 'required|integer|between:0,100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'producto_id' => 'required|exists:productos,id',
        ]);

        $promocion->update($request->all());

        return Inertia::render('Admin/Promocion', [
            'promociones' => Promocion::with('producto')->get(),
            'productos' => Producto::all(),
        ]);
    }

    public function destroy(Promocion $promocion)
    {
        $promocion->delete();

        return Inertia::render('Admin/Promocion', [
            'promociones' => Promocion::with('producto')->get(),
            'productos' => Producto::all(),
        ]);
    }
}
