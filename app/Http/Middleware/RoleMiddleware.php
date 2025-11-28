<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder.');
        }

        $user = Auth::user();
        
        // Log para debugging (opcional)
        Log::info('Role Middleware - Usuario: ' . $user->email . ', Rol: ' . ($user->rol ?? 'SIN ROL') . ', Roles requeridos: ' . implode(',', $roles));

        // Verificar si el usuario tiene rol asignado
        if (!$user->rol) {
            abort(403, 'Tu cuenta no tiene un rol asignado. Contacta al administrador.');
        }

        // Verificar si el usuario tiene alguno de los roles requeridos
        if (!in_array($user->rol, $roles)) {
            abort(403, 'No tienes permisos para acceder a esta página. Rol requerido: ' . implode(' o ', $roles) . '. Tu rol: ' . $user->rol);
        }

        return $next($request);
    }
}