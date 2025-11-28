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
        'fecha_pago',
        'tipo_pago',
        'estado_pago'
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

    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
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

    // ===== MÉTODOS PARA PAGOS A CRÉDITO =====

    public function esCredito(): bool
    {
        return $this->tipo_pago === 'credito';
    }

    public function esContado(): bool
    {
        return $this->tipo_pago === 'contado';
    }

    public function cuotasPendientes()
    {
        return $this->cuotas()->where('estado', 'pendiente')->get();
    }

    public function siguienteCuotaPendiente()
    {
        return $this->cuotas()
            ->where('estado', 'pendiente')
            ->orderBy('numero_cuota')
            ->first();
    }

    public function montoRestante(): float
    {
        return (float) $this->cuotas()->where('estado', 'pendiente')->sum('monto');
    }

    public function actualizarEstadoPago(): void
    {
        if ($this->esContado()) {
            return;
        }

        $totalCuotas = $this->cuotas()->count();
        $cuotasPagadas = $this->cuotas()->where('estado', 'pagado')->count();
        
        if ($cuotasPagadas === 0) {
            $this->estado_pago = 'pendiente';
        } elseif ($cuotasPagadas < $totalCuotas) {
            $this->estado_pago = 'parcial';
        } else {
            $this->estado_pago = 'completado';
            $this->estado = 'pagado'; // Marcar venta como pagada cuando todas las cuotas están pagadas
        }
        
        $this->save();
    }

    /**
     * Crear plan de pagos 50/50
     */
    public function crearPlanPagos(int $diasSegundaCuota = 30): array
    {
        $montoPorCuota = round($this->total / 2, 2);
        
        // Ajustar por redondeo - la segunda cuota toma cualquier diferencia
        $primeraCuota = $montoPorCuota;
        $segundaCuota = $this->total - $primeraCuota;

        $cuotas = [];

        // Primera cuota (50%) - pago inmediato
        $cuotas[] = Cuota::create([
            'venta_id' => $this->id,
            'numero_cuota' => 1,
            'monto' => $primeraCuota,
            'fecha_vencimiento' => now()->toDateString(),
            'estado' => 'pendiente',
        ]);

        // Segunda cuota (50%) - fecha futura
        $cuotas[] = Cuota::create([
            'venta_id' => $this->id,
            'numero_cuota' => 2,
            'monto' => $segundaCuota,
            'fecha_vencimiento' => now()->addDays($diasSegundaCuota)->toDateString(),
            'estado' => 'pendiente',
        ]);

        return $cuotas;
    }
}
