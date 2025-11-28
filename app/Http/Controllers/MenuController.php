<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::all();
        
        // Siempre devolver JSON para las peticiones del frontend
        return response()->json([
            'menus' => $menus
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'url' => 'required|string|max:255'
        ], [
            'nombre.required' => 'El nombre del menú es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'url.required' => 'La URL es obligatoria.',
            'url.string' => 'La URL debe ser texto.',
            'url.max' => 'La URL no puede exceder 255 caracteres.'
        ]);

        Menu::create($validated);

        return redirect()->back()->with('success', 'Menú creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'url' => 'required|string|max:255'
        ], [
            'nombre.required' => 'El nombre del menú es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'url.required' => 'La URL es obligatoria.',
            'url.string' => 'La URL debe ser texto.',
            'url.max' => 'La URL no puede exceder 255 caracteres.'
        ]);

        $menu->update($validated);

        return redirect()->back()->with('success', 'Menú actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return redirect()->back()->with('success', 'Menú eliminado exitosamente.');
    }
}