<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $table = 'users';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password',
        'edad',
        'celular',
        'sexo',
        'rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // MÉTODOS PARA ROLES SIMPLES
    public function hasRole($role)
    {
        return $this->rol === $role;
    }

    public function isAdmin()
    {
        return $this->rol === 'admin';
    }

    public function isCliente()
    {
        return $this->rol === 'cliente';
    }

    public function isProveedor()
    {
        return $this->rol === 'proveedor';
    }

    public function assignRole($role)
    {
        $this->rol = $role;
        $this->save();
    }

    // SCOPES PARA CONSULTAS
    public function scopeAdmins($query)
    {
        return $query->where('rol', 'admin');
    }

    public function scopeClientes($query)
    {
        return $query->where('rol', 'cliente');
    }

    public function scopeProveedores($query)
    {
        return $query->where('rol', 'proveedor');
    }

    // RELACIONES
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    public function carritoItems()
    {
        return $this->hasMany(Carrito::class, 'usuario_id');
    }

    public function productosRegistrados()
    {
        return $this->hasMany(Producto::class, 'usuario_registrador_id');
    }

    public function productosProveedore()
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }

    // CONSTANTES PARA ROLES
    const ROLES = [
        'admin' => 'Administrador',
        'cliente' => 'Cliente',
        'proveedor' => 'Proveedor',
    ];

    public static function getRolesList()
    {
        return self::ROLES;
    }

    // MÉTODOS PARA CARRITO
    public function contarCarrito()
    {
        return $this->carritoItems()->sum('cantidad');
    }

    public function totalCarrito()
    {
        return $this->carritoItems()
            ->with('producto')
            ->get()
            ->sum(function($item) {
                return $item->cantidad * $item->precio_unitario;
            });
    }
}