<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with(['categoria', 'proveedor'])
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => (float) $producto->precio,
                    'cantidad' => (int) $producto->cantidad,
                    'imagen' => $producto->imagen,
                    'imagen_url' => $producto->imagen ? asset('storage/productos/' . $producto->imagen) : config('app.url') . '/images/producto-placeholder.jpg',
                    'categoria' => $producto->categoria,
                    'proveedor' => $producto->proveedor,
                    'categoria_id' => $producto->categoria_id,
                    'proveedor_id' => $producto->proveedor_id,
                ];
            });

        $categorias = Categoria::all();
        $usuarios = User::all();
        
        return Inertia::render('Admin/Productos', [
            'productos' => $productos,
            'categorias' => $categorias,
            'usuarios' => $usuarios,
        ]);
    }

    public function store(Request $request)
    {
        // Debug temporal
        \Log::info('Request completo:', $request->all());
        \Log::info('Archivos:', $request->allFiles());
        if ($request->hasFile('imagen')) {
            \Log::info('Archivo imagen detectado:', [
                'name' => $request->file('imagen')->getClientOriginalName(),
                'mime' => $request->file('imagen')->getMimeType(),
                'size' => $request->file('imagen')->getSize()
            ]);
        } else {
            \Log::info('NO se detectó archivo imagen');
        }
        
        $messages = [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede exceder 255 caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio debe ser mayor a 0.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser mayor o igual a 0.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser jpg, jpeg, png o gif.',
            'imagen.max' => 'La imagen no puede ser mayor a 2MB.',
        ];

        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0.01',
            'cantidad' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'proveedor_id' => 'required|exists:users,id',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], $messages);

        $usuario = Auth::user();

        // Manejar subida de imagen
        $imagenNombre = null;
        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $imagenNombre = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
            
            // Asegurar que el directorio existe
            if (!Storage::disk('public')->exists('productos')) {
                Storage::disk('public')->makeDirectory('productos');
            }
            
            $imagen->storeAs('productos', $imagenNombre, 'public');
        }

        Producto::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'imagen' => $imagenNombre,
            'precio' => $validated['precio'],
            'cantidad' => $validated['cantidad'],
            'categoria_id' => $validated['categoria_id'],
            'usuario_registrador_id' => $usuario->id,
            'proveedor_id' => $validated['proveedor_id'],
        ]);

        return redirect()->route('admin.productos')->with('success', 'Producto creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $messages = [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede exceder 255 caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio debe ser mayor a 0.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser mayor o igual a 0.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser jpg, jpeg, png o gif.',
            'imagen.max' => 'La imagen no puede ser mayor a 2MB.',
        ];

        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0.01',
            'cantidad' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'proveedor_id' => 'required|exists:users,id',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], $messages);

        // Manejar actualización de imagen
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen && Storage::disk('public')->exists('productos/' . $producto->imagen)) {
                Storage::disk('public')->delete('productos/' . $producto->imagen);
            }
            
            $imagen = $request->file('imagen');
            $imagenNombre = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
            
            // Asegurar que el directorio existe
            if (!Storage::disk('public')->exists('productos')) {
                Storage::disk('public')->makeDirectory('productos');
            }
            
            $imagen->storeAs('productos', $imagenNombre, 'public');
            $validated['imagen'] = $imagenNombre;
        }

        $producto->update($validated);

        return redirect()->route('admin.productos')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        
        // Eliminar imagen si existe
        if ($producto->imagen && Storage::disk('public')->exists('productos/' . $producto->imagen)) {
            Storage::disk('public')->delete('productos/' . $producto->imagen);
        }
        
        $producto->delete();

        return redirect()->route('admin.productos')->with('success', 'Producto eliminado exitosamente.');
    }

    public function getAllProducts()
    {
        $products = Producto::with(['categoria'])
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => (float) $producto->precio,
                    'cantidad' => (int) $producto->cantidad,
                    'imagen_url' => $producto->imagen ? asset('storage/productos/' . $producto->imagen) : config('app.url') . '/images/producto-placeholder.jpg',
                    'categoria' => $producto->categoria,
                    'tiene_stock' => $producto->cantidad > 0,
                ];
            });

        return response()->json($products);
    }

    // Alias para la API
    public function apiIndex()
    {
        return $this->getAllProducts();
    }

    public function catalogo()
    {
        $productos = Producto::with(['categoria', 'proveedor'])
            ->where('cantidad', '>', 0)
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => (float) $producto->precio,
                    'cantidad' => (int) $producto->cantidad,
                    'imagen_url' => $producto->imagen ? asset('storage/productos/' . $producto->imagen) : config('app.url') . '/images/producto-placeholder.jpg',
                    'categoria' => $producto->categoria,
                    'tiene_stock' => true,
                ];
            });

        $categorias = Categoria::all();
        
        return Inertia::render('Catalogo', [
            'productos' => $productos,
            'categorias' => $categorias,
            'usuario' => Auth::user(),
        ]);
    }
}