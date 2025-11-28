<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha_venta', 
        'total', 
        'estado', 
        'usuario_id',
        'payment_number',
        'transaction_id',
        'metodo_pago',
        'fecha_pago'
    ];

    protected $casts = [
        'fecha_venta' => 'date',
        'fecha_pago' => 'datetime',
        'total' => 'decimal:2'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalleventas()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    /**
     * Buscar venta por payment_number (PedidoID de PagoFacil)
     */
    public static function findByPaymentNumber($paymentNumber)
    {
        return self::where('payment_number', $paymentNumber)->first();
    }

    /**
     * Buscar venta por transaction_id
     */
    public static function findByTransactionId($transactionId)
    {
        return self::where('transaction_id', $transactionId)->first();
    }
}
