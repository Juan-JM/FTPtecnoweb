<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen',
        'precio',
        'cantidad',
        'categoria_id',
        'usuario_registrador_id',
        'proveedor_id',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'cantidad' => 'integer',
    ];

    protected $appends = ['imagen_url'];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(User::class, 'proveedor_id');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'usuario_registrador_id');
    }

    // Accesor para URL de imagen
    public function getImagenUrlAttribute()
    {
        if ($this->imagen) {
            return asset('storage/productos/' . $this->imagen);
        }
        return asset('images/producto-placeholder.jpg');
    }

    // Método para verificar si hay stock
    public function tieneStock($cantidadSolicitada = 1)
    {
        return $this->cantidad >= $cantidadSolicitada;
    }

    // Método para reducir stock
    public function reducirStock($cantidad)
    {
        if ($this->tieneStock($cantidad)) {
            $this->cantidad -= $cantidad;
            $this->save();
            return true;
        }
        return false;
    }

    // Scope para productos en stock
    public function scopeEnStock($query)
    {
        return $query->where('cantidad', '>', 0);
    }

    // Scope para productos por categoría
    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }
}