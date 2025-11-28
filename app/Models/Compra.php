<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

protected $table = 'compra';

    protected $fillable = [
        'usuario_id',
        'fecha_compra',
        'total',
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'total' => 'decimal:2',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class);
    }

    // Métodos auxiliares
    public function calcularTotal()
    {
        return $this->detalles->sum(function($detalle) {
            return $detalle->cantidad * $detalle->precio_unitario;
        });
    }

    public function totalProductos()
    {
        return $this->detalles->sum('cantidad');
    }

    // Scopes
    public function scopeDelUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_compra', [$fechaInicio, $fechaFin]);
    }
}