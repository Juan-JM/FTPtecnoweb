<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visita;

class ContadorDeVisitas
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Crear registro de visita con más información
            Visita::create([
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'pagina' => $request->path()
            ]);
        } catch (\Exception $e) {
            // Si falla el contador, continuar sin bloquear la petición
            \Log::error('Error en contador de visitas: ' . $e->getMessage());
        }

        return $next($request);
    }
}