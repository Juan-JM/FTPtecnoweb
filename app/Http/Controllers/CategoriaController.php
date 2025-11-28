<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    public function index()
    {
        // ✅ CORRECCIÓN: Usar el nombre correcto de la relación
        $categorias = Categoria::with('usuarioAdministrador')->get();
        $usuarios = User::where('rol', 'admin')->get(); // Solo admins
        
        return Inertia::render('Admin/Categorias', [
            'categorias' => $categorias,
            'usuarios' => $usuarios
        ]);
    }

    public function store(Request $request)
    {
        $messages = [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede exceder 30 caracteres.',
            'nombre.unique' => 'Ya existe una categoría con este nombre.',
        ];

        $validated = $request->validate([
            'nombre' => 'required|string|max:30|unique:categorias,nombre',
        ], $messages);

        try {
            Categoria::create([
                'nombre' => $validated['nombre'],
                'usuario_administrador_id' => Auth::id(),
            ]);

            return redirect()->route('admin.categorias')->with('success', 'Categoría creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.categorias')
                ->withErrors(['error' => 'Error al crear la categoría: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $messages = [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede exceder 30 caracteres.',
            'nombre.unique' => 'Ya existe una categoría con este nombre.',
        ];

        $validated = $request->validate([
            'nombre' => 'required|string|max:30|unique:categorias,nombre,' . $id,
        ], $messages);

        try {
            $categoria->update($validated);

            return redirect()->route('admin.categorias')->with('success', 'Categoría actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.categorias')
                ->withErrors(['error' => 'Error al actualizar la categoría: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $nombreCategoria = $categoria->nombre;
            
            // Verificar si la categoría tiene productos asociados
            $productosCount = $categoria->productos()->count();
            if ($productosCount > 0) {
                return redirect()->route('admin.categorias')
                    ->withErrors(['error' => "No se puede eliminar la categoría '{$nombreCategoria}' porque tiene {$productosCount} producto(s) asociado(s)."]);
            }

            $categoria->delete();

            return redirect()->route('admin.categorias')->with('success', "Categoría '{$nombreCategoria}' eliminada exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('admin.categorias')
                ->withErrors(['error' => 'Error al eliminar la categoría: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $categoria = Categoria::with(['usuarioAdministrador', 'productos'])->findOrFail($id);
        
        return Inertia::render('Admin/CategoriaDetalle', [
            'categoria' => $categoria
        ]);
    }
}