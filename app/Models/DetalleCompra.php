<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    use HasFactory;

    protected $table = 'detalle_compra';

    protected $fillable = [
        'compra_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    // Relaciones
    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Accesor para subtotal
    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }

    // Métodos auxiliares
    public function actualizarInventario()
    {
        $producto = $this->producto;
        $producto->cantidad += $this->cantidad;
        $producto->save();

        // Crear movimiento de inventario
        Inventario::create([
            'producto_id' => $this->producto_id,
            'cantidad_disponible' => $producto->cantidad,
            'fecha_ultima_actualizacion' => now(),
            'tipo_movimiento' => 'entrada',
            'cantidad_movimiento' => $this->cantidad,
        ]);
    }

    public function revertirInventario()
    {
        $producto = $this->producto;
        
        if ($producto->cantidad >= $this->cantidad) {
            $producto->cantidad -= $this->cantidad;
            $producto->save();

            // Crear movimiento de inventario de salida
            Inventario::create([
                'producto_id' => $this->producto_id,
                'cantidad_disponible' => $producto->cantidad,
                'fecha_ultima_actualizacion' => now(),
                'tipo_movimiento' => 'salida',
                'cantidad_movimiento' => $this->cantidad,
            ]);

            return true;
        }

        return false;
    }
}