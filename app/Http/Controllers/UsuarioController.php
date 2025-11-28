<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index()
    {
        // Solo admins pueden ver la lista de usuarios
        $usuarios = User::select('id', 'nombre', 'apellido', 'email', 'rol', 'created_at')->get();
        $roles = User::getRolesList();
        
        return Inertia::render('Admin/Usuarios', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'currentUser' => Auth::user()
        ]);
    }

    public function asignarRol(Request $request, $id)
    {
        $request->validate([
            'rol' => 'required|in:admin,cliente,proveedor'
        ], [
            'rol.required' => 'Debe seleccionar un rol.',
            'rol.in' => 'El rol seleccionado no es válido.'
        ]);

        $usuario = User::findOrFail($id);
        
        // Evitar que un admin se quite su propio rol admin si es el único
        if ($usuario->id === Auth::id() && $usuario->rol === 'admin' && $request->rol !== 'admin') {
            $otrosAdmins = User::where('rol', 'admin')->where('id', '!=', $usuario->id)->count();
            if ($otrosAdmins === 0) {
                return back()->withErrors(['rol' => 'No puedes quitar tu rol de administrador siendo el único admin del sistema.']);
            }
        }

        $rolAnterior = $usuario->rol;
        $usuario->assignRole($request->rol);

        return back()->with('success', "Rol de {$usuario->nombre} {$usuario->apellido} cambiado de '{$rolAnterior}' a '{$request->rol}' exitosamente.");
    }

    public function create()
    {
        $roles = User::getRolesList();
        return Inertia::render('Admin/CrearUsuario', [
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'edad' => 'required|integer|min:1|max:120',
            'celular' => 'required|integer',
            'sexo' => 'required|in:M,F',
            'rol' => 'required|in:admin,cliente,proveedor'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'edad.required' => 'La edad es obligatoria.',
            'edad.integer' => 'La edad debe ser un número.',
            'edad.min' => 'La edad debe ser mayor a 0.',
            'edad.max' => 'La edad debe ser menor a 120.',
            'celular.required' => 'El número de celular es obligatorio.',
            'celular.integer' => 'El celular debe ser un número.',
            'sexo.required' => 'El sexo es obligatorio.',
            'sexo.in' => 'El sexo debe ser M (Masculino) o F (Femenino).',
            'rol.required' => 'Debe seleccionar un rol.',
            'rol.in' => 'El rol seleccionado no es válido.'
        ]);

        $validated['password'] = bcrypt($validated['password']);

        User::create($validated);

        return redirect()->route('admin.usuarios')->with('success', 'Usuario creado exitosamente.');
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        
        // Evitar que un admin se elimine a sí mismo si es el único
        if ($usuario->id === Auth::id() && $usuario->rol === 'admin') {
            $otrosAdmins = User::where('rol', 'admin')->where('id', '!=', $usuario->id)->count();
            if ($otrosAdmins === 0) {
                return back()->withErrors(['error' => 'No puedes eliminar tu propia cuenta siendo el único administrador.']);
            }
        }

        $nombre = $usuario->nombre . ' ' . $usuario->apellido;
        $usuario->delete();

        return back()->with('success', "Usuario {$nombre} eliminado exitosamente.");
    }
}