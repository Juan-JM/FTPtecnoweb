<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $table = 'carrito';

    protected $fillable = [
        'usuario_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class);
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

    // Método estático para obtener carrito de usuario
    public static function carritoUsuario($usuarioId)
    {
        return self::with(['producto.categoria'])
            ->where('usuario_id', $usuarioId)
            ->get()
            ->map(function ($item) {
                // Mapear igual que en el catálogo
                $item->producto->imagen_url = $item->producto->imagen 
                    ? asset('storage/productos/' . $item->producto->imagen) 
                    : config('app.url') . '/images/producto-placeholder.jpg';
                return $item;
            });
    }

    // Método estático para total del carrito
    public static function totalCarrito($usuarioId)
    {
        return self::where('usuario_id', $usuarioId)
            ->sum(\DB::raw('cantidad * precio_unitario'));
    }

    // Método estático para contar items del carrito
    public static function contarItems($usuarioId)
    {
        return self::where('usuario_id', $usuarioId)->sum('cantidad');
    }

    // Método para agregar al carrito
    public static function agregarProducto($usuarioId, $productoId, $cantidad = 1)
    {
        $producto = Producto::findOrFail($productoId);
        
        // Verificar stock
        if (!$producto->tieneStock($cantidad)) {
            return ['success' => false, 'message' => 'No hay suficiente stock disponible'];
        }

        $carritoItem = self::where('usuario_id', $usuarioId)
            ->where('producto_id', $productoId)
            ->first();

        if ($carritoItem) {
            // Si ya existe, actualizar cantidad
            $nuevaCantidad = $carritoItem->cantidad + $cantidad;
            
            if (!$producto->tieneStock($nuevaCantidad)) {
                return ['success' => false, 'message' => 'No hay suficiente stock para esa cantidad'];
            }
            
            $carritoItem->cantidad = $nuevaCantidad;
            $carritoItem->save();
        } else {
            // Crear nuevo item
            self::create([
                'usuario_id' => $usuarioId,
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'precio_unitario' => $producto->precio,
            ]);
        }

        return ['success' => true, 'message' => 'Producto agregado al carrito'];
    }
}