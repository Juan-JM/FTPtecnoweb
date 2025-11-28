<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha_pago',
        'monto',
        'metodo_pago',
        'venta_id',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
