<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visita;

class ContadorController extends Controller
{
    public function obtenerContadores(Request $request)
    {
        $urlActual = $request->get('url', $request->url());
        
        return response()->json([
            'contadorPagina' => Visita::contadorPagina($urlActual),
            'contadorTotal' => Visita::contadorTotal(),
            'url' => $urlActual
        ]);
    }
}