<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'numero_cuota',
        'monto',
        'fecha_vencimiento',
        'fecha_pago',
        'estado',
        'payment_number',
        'transaction_id',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
        'monto' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function estaPagada(): bool
    {
        return $this->estado === 'pagado';
    }

    public function estaVencida(): bool
    {
        return !$this->estaPagada() && $this->fecha_vencimiento < now();
    }

    /**
     * Buscar cuota por payment_number
     */
    public static function findByPaymentNumber($paymentNumber)
    {
        return self::where('payment_number', $paymentNumber)->first();
    }
}
