<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'usuario_administrador_id'];

    // ✅ CORRECCIÓN: Nombre correcto de la relación
    public function usuarioAdministrador()
    {
        return $this->belongsTo(User::class, 'usuario_administrador_id');
    }

    // ✅ ALIAS: Mantener el método anterior por compatibilidad
    public function administrador()
    {
        return $this->usuarioAdministrador();
    }

    // ✅ RELACIÓN: Productos que pertenecen a esta categoría
    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }

    // ✅ SCOPE: Categorías de un administrador específico
    public function scopeDeAdministrador($query, $adminId)
    {
        return $query->where('usuario_administrador_id', $adminId);
    }

    // ✅ ACCESSOR: Nombre completo del administrador
    public function getNombreAdministradorAttribute()
    {
        return $this->usuarioAdministrador 
            ? $this->usuarioAdministrador->nombre . ' ' . $this->usuarioAdministrador->apellido
            : 'Sin asignar';
    }
}