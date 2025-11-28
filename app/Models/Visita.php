<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visita extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'ip', 'user_agent', 'pagina'];

    // Método para obtener contador de una página específica
    public static function contadorPagina($url = null)
    {
        if ($url) {
            return self::where('url', $url)->count();
        }
        return self::count();
    }

    // Método para obtener contador total
    public static function contadorTotal()
    {
        return self::count();
    }
}