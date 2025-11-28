<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promocion extends Model
{
    use HasFactory;

    // Especificar los campos que pueden ser asignados masivamente
    protected $fillable = [
        'nombre',
        'descripcion',
        'descuento_porcentaje',
        'fecha_inicio',
        'fecha_fin',
        'producto_id',
    ];

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
