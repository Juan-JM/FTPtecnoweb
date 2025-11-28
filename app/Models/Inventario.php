<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario';

    protected $fillable = [
        'cantidad_disponible',
        'fecha_ultima_actualizacion',
        'tipo_movimiento',
        'cantidad_movimiento',
        'producto_id',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }
}
