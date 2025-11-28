<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PagoFacilService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Carrito;
use App\Models\Cuota;

class ConsumirServicioController extends Controller
{
    protected $pagoFacilService;

    public function __construct(PagoFacilService $pagoFacilService)
    {
        $this->pagoFacilService = $pagoFacilService;
    }

    public function recolectarDatos(Request $request)
    {
        try {
            // Log de datos recibidos
            Log::info('=== DATOS RECIBIDOS EN recolectarDatos ===', [
                'all_data' => $request->all(),
                'content_type' => $request->header('Content-Type')
            ]);
            
            // Validar datos requeridos
            $validatedData = $request->validate([
                'tnTelefono' => 'required|string|min:8|max:15',
                'tcRazonSocial' => 'required|string|max:100', 
                'tcCiNit' => 'required|string|max:20',
                'tcCorreo' => 'required|email|max:100',
                'tnMonto' => 'required|numeric|min:0.01',
                'tnTotal' => 'required|numeric|min:0.01',
                'taPedidoDetalle' => 'nullable|string|max:500',
                'tcSerial' => 'nullable|string',
                'tnDescuento' => 'nullable|numeric',
                'tnTipoServicio' => 'nullable|numeric',
                'tipoPago' => 'nullable|in:contado,credito',
                'diasSegundaCuota' => 'nullable|integer|min:7|max:90',
            ]);
            
            Log::info('Iniciando proceso de pago PagoFacil', ['datos_validados' => $validatedData]);

            // Determinar tipo de pago
            $tipoPago = $validatedData['tipoPago'] ?? 'contado';
            $diasSegundaCuota = $validatedData['diasSegundaCuota'] ?? 30;
            $totalOriginal = floatval($validatedData['tnTotal']);
            
            // Si es crédito, el QR se genera por el 50% (primera cuota)
            $montoQr = $tipoPago === 'credito' ? round($totalOriginal / 2, 2) : $totalOriginal;
            $detalleExtra = $tipoPago === 'credito' ? ' (Cuota 1 de 2)' : '';

            // Preparar datos para el servicio
            $datosQr = [
                'nombre_cliente' => $validatedData['tcRazonSocial'],
                'document_id' => preg_replace('/[^0-9]/', '', $validatedData['tcCiNit']),
                'telefono' => preg_replace('/[^0-9]/', '', $validatedData['tnTelefono']),
                'email' => $validatedData['tcCorreo'],
                'numero_pedido' => 'TECNO_' . time() . '_' . rand(1000, 9999),
                'monto' => $montoQr,
                'detalle_pedido' => ($validatedData['taPedidoDetalle'] ?? 'Compra en TecnoWeb') . $detalleExtra,
            ];

            // Generar QR usando el servicio moderno
            $resultado = $this->pagoFacilService->generarQr($datosQr);

            if ($resultado) {
                Log::info('QR generado exitosamente', ['resultado' => $resultado]);
                
                // PagoFacil devuelve qrBase64, mapeamos a qrImage para el frontend
                $qrImage = $resultado['qrBase64'] ?? $resultado['qrImage'] ?? null;
                $transactionId = $resultado['transactionId'] ?? null;
                $paymentNumber = $resultado['paymentNumber'] ?? null;
                
                Log::info('Datos extraídos del QR:', [
                    'transactionId' => $transactionId,
                    'paymentNumber' => $paymentNumber,
                    'qrImage_exists' => !empty($qrImage),
                    'tipoPago' => $tipoPago
                ]);
                
                // Crear venta en estado PENDIENTE
                $usuarioId = Auth::id();
                Log::info('Usuario autenticado:', ['usuario_id' => $usuarioId]);
                
                $ventaCreada = null;
                $cuotaInfo = null;
                
                if ($usuarioId && $paymentNumber) {
                    $ventaCreada = $this->crearVentaPendiente(
                        $usuarioId, 
                        $paymentNumber, 
                        $transactionId, 
                        $totalOriginal,
                        $tipoPago,
                        $diasSegundaCuota
                    );
                    Log::info('Resultado de crear venta:', ['venta' => $ventaCreada ? $ventaCreada->id : 'NULL']);
                    
                    // Si es crédito, devolver info de las cuotas
                    if ($ventaCreada && $tipoPago === 'credito') {
                        $cuotaInfo = [
                            'tipo_pago' => 'credito',
                            'cuotas' => $ventaCreada->cuotas->map(function($cuota) {
                                return [
                                    'numero' => $cuota->numero_cuota,
                                    'monto' => $cuota->monto,
                                    'fecha_vencimiento' => $cuota->fecha_vencimiento->format('Y-m-d'),
                                    'estado' => $cuota->estado,
                                ];
                            }),
                            'monto_primera_cuota' => $montoQr,
                            'dias_segunda_cuota' => $diasSegundaCuota,
                        ];
                    }
                } else {
                    Log::warning('No se pudo crear venta - falta usuarioId o paymentNumber', [
                        'usuarioId' => $usuarioId,
                        'paymentNumber' => $paymentNumber
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'values' => $resultado,
                        'qrImage' => $qrImage,
                        'qrBase64' => $qrImage,
                        'transactionId' => $transactionId,
                        'paymentNumber' => $paymentNumber,
                        'amount' => $montoQr,
                        'totalOriginal' => $totalOriginal,
                        'expirationDate' => $resultado['expirationDate'] ?? null,
                        'ventaCreada' => $ventaCreada ? $ventaCreada->id : null,
                        'tipoPago' => $tipoPago,
                        'cuotaInfo' => $cuotaInfo,
                    ]
                ]);
            } else {
                Log::error('Error generando QR - no se obtuvo resultado');
                return response()->json([
                    'success' => false,
                    'error' => true,
                    'message' => 'No se pudo generar el código QR. Por favor intenta de nuevo.',
                    'debug_info' => [
                        'service_url' => config('services.pagofacil.base_url'),
                        'request_data' => $datosQr
                    ]
                ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación en recolectarDatos:', [
                'errors' => $e->errors(),
                'received_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'Datos inválidos',
                'errors' => $e->errors(),
                'received_fields' => array_keys($request->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en recolectarDatos:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $datosQr ?? 'No disponible'
            ]);
            
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
                'debug_info' => [
                    'service_url' => config('services.pagofacil.base_url'),
                    'error_line' => $e->getLine(),
                    'error_file' => basename($e->getFile()),
                    'request_data' => $datosQr ?? null,
                    'config_check' => [
                        'base_url' => config('services.pagofacil.base_url'),
                        'token_service_set' => !empty(config('services.pagofacil.token_service')),
                        'token_secret_set' => !empty(config('services.pagofacil.token_secret'))
                    ]
                ]
            ], 500);
        }
    }

    public function consultarEstado(Request $request)
    {
        try {
            $transaccionId = $request->input('transactionId');
            $paymentNumber = $request->input('paymentNumber');
            
            // Primero verificar en nuestra base de datos si el pago ya fue confirmado
            $venta = null;
            if ($paymentNumber) {
                $venta = Venta::where('payment_number', $paymentNumber)->first();
            } elseif ($transaccionId) {
                $venta = Venta::where('transaction_id', $transaccionId)->first();
            }
            
            // Si la venta existe y está pagada, devolver confirmación inmediata
            if ($venta && $venta->estado === 'pagado') {
                Log::info('Venta encontrada como pagada en BD:', ['venta_id' => $venta->id]);
                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => 2, // 2 = pagado
                        'message' => 'Pago confirmado',
                        'venta_id' => $venta->id
                    ]
                ]);
            }
            
            // Si tenemos transactionId, consultar a PagoFacil
            if ($transaccionId) {
                $resultado = $this->pagoFacilService->queryTransaction($transaccionId);
                
                if ($resultado) {
                    return response()->json([
                        'success' => true,
                        'data' => $resultado
                    ]);
                }
            }
            
            // Retornar estado pendiente si no hay información
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => 1, // 1 = pendiente
                    'message' => 'Esperando pago'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error consultando estado:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'error' => true,
                'message' => 'Error al consultar estado: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Crear venta pendiente antes de que el usuario pague
     */
    private function crearVentaPendiente($usuarioId, $paymentNumber, $transactionId, $total, $tipoPago = 'contado', $diasSegundaCuota = 30)
    {
        try {
            Log::info('=== INICIANDO crearVentaPendiente ===', [
                'usuarioId' => $usuarioId,
                'paymentNumber' => $paymentNumber,
                'transactionId' => $transactionId,
                'total' => $total,
                'tipoPago' => $tipoPago,
                'diasSegundaCuota' => $diasSegundaCuota
            ]);
            
            // Verificar si ya existe una venta con este payment_number
            $ventaExistente = Venta::where('payment_number', $paymentNumber)->first();
            if ($ventaExistente) {
                Log::info('Venta ya existe para payment_number:', ['payment_number' => $paymentNumber, 'venta_id' => $ventaExistente->id]);
                return $ventaExistente;
            }

            DB::beginTransaction();

            // Crear la venta en estado pendiente
            $venta = Venta::create([
                'fecha_venta' => now()->toDateString(),
                'total' => $total,
                'estado' => 'pendiente',
                'usuario_id' => $usuarioId,
                'payment_number' => $paymentNumber,
                'transaction_id' => $transactionId,
                'tipo_pago' => $tipoPago,
                'estado_pago' => 'pendiente',
            ]);
            
            Log::info('Venta creada en BD:', ['venta_id' => $venta->id, 'venta' => $venta->toArray()]);

            // Obtener items del carrito y crear detalles
            $carritoItems = Carrito::where('usuario_id', $usuarioId)->with('producto')->get();
            Log::info('Items en carrito:', ['count' => $carritoItems->count()]);
            
            foreach ($carritoItems as $item) {
                $detalle = DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item->producto_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                ]);
                Log::info('Detalle venta creado:', ['detalle_id' => $detalle->id]);
            }

            // Si es crédito, crear plan de pagos (2 cuotas 50/50)
            if ($tipoPago === 'credito') {
                $cuotas = $venta->crearPlanPagos($diasSegundaCuota);
                
                // Asignar payment_number a la primera cuota
                $cuotas[0]->update([
                    'payment_number' => $paymentNumber,
                    'transaction_id' => $transactionId,
                ]);
                
                Log::info('Plan de pagos creado:', [
                    'cuotas' => count($cuotas),
                    'cuota1_monto' => $cuotas[0]->monto,
                    'cuota2_monto' => $cuotas[1]->monto,
                    'cuota2_vencimiento' => $cuotas[1]->fecha_vencimiento,
                ]);
            }

            DB::commit();
            
            // Recargar la venta con cuotas
            $venta->load('cuotas');
            
            Log::info('✅ Venta pendiente creada exitosamente:', [
                'venta_id' => $venta->id,
                'payment_number' => $paymentNumber,
                'transaction_id' => $transactionId,
                'tipo_pago' => $tipoPago
            ]);

            return $venta;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error creando venta pendiente:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // No lanzamos excepción para no interrumpir el flujo de QR
            return null;
        }
    }
    
    /**
     * Obtener nombre del método de pago
     */
    private function nombreMetodoPago($codigo)
    {
        $metodos = [
            1 => 'Tarjeta',
            2 => 'Tigo Money',
            3 => 'Transferencia',
            4 => 'QR',
        ];
        return $metodos[$codigo] ?? 'Otro';
    }

    public function urlCallback(Request $request)
    {
        // Log detallado para debug
        $logData = [
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_content' => $request->getContent()
        ];
        
        $logFile = storage_path('logs/pagofacil_callbacks.log');
        file_put_contents($logFile, "\n\n=== " . now()->toDateTimeString() . " ===\n" . json_encode($logData, JSON_PRETTY_PRINT), FILE_APPEND);
        
        Log::channel('single')->info('=== CALLBACK PAGOFACIL RECIBIDO ===', $logData);
        
        try {
            $data = $request->all();
            if (empty($data)) {
                $data = json_decode($request->getContent(), true) ?? [];
            }
            
            // PagoFacil envía PedidoID que es nuestro paymentNumber
            $pedidoId = $data['PedidoID'] ?? $data['pedidoId'] ?? null;
            $fecha = $data['Fecha'] ?? $data['fecha'] ?? null;
            $hora = $data['Hora'] ?? $data['hora'] ?? null;
            $estado = $data['Estado'] ?? $data['estado'] ?? null;
            $metodoPago = $data['MetodoPago'] ?? $data['metodoPago'] ?? null;

            Log::info('Datos del callback procesados:', [
                'PedidoID' => $pedidoId,
                'Fecha' => $fecha,
                'Hora' => $hora,
                'Estado' => $estado,
                'MetodoPago' => $metodoPago,
            ]);

            if (!$pedidoId) {
                Log::error('Callback sin PedidoID');
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => 'PedidoID no encontrado',
                    'messageMostrar' => 0,
                    'messageSistema' => '',
                    'values' => false
                ], 200);
            }

            // Primero buscar si es una cuota (para pagos a crédito)
            $cuota = Cuota::where('payment_number', $pedidoId)->first();
            
            // Si es una cuota, procesar pago de cuota
            if ($cuota) {
                return $this->procesarPagoCuota($cuota, $estado, $metodoPago);
            }

            // Si no es cuota, buscar la venta directamente (pago al contado)
            $venta = Venta::where('payment_number', $pedidoId)->first();
            
            Log::info('Búsqueda venta:', [
                'pedidoId' => $pedidoId,
                'encontrada' => $venta ? 'SÍ' : 'NO',
                'venta_id' => $venta->id ?? null,
                'estado_actual' => $venta->estado ?? null,
                'tipo_pago' => $venta->tipo_pago ?? null
            ]);
            
            if (!$venta) {
                Log::warning('Venta no encontrada para PedidoID:', ['pedido_id' => $pedidoId]);
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => 'Venta no encontrada',
                    'messageMostrar' => 0,
                    'messageSistema' => 'No existe venta con payment_number: ' . $pedidoId,
                    'values' => false
                ], 200);
            }

            // Si la venta es a crédito y tiene cuotas, procesar la primera cuota
            if ($venta->esCredito() && $venta->cuotas()->count() > 0) {
                $primeraCuota = $venta->cuotas()->where('numero_cuota', 1)->first();
                if ($primeraCuota && $primeraCuota->estado === 'pendiente') {
                    // Actualizar payment_number de la cuota si no lo tiene
                    if (!$primeraCuota->payment_number) {
                        $primeraCuota->update(['payment_number' => $pedidoId]);
                    }
                    return $this->procesarPagoCuota($primeraCuota, $estado, $metodoPago);
                }
            }

            // Estado 2 = Pago exitoso (para ventas al contado)
            if ($estado == 2) {
                Log::info('✅ PAGO CONFIRMADO - Actualizando venta:', ['venta_id' => $venta->id]);
                
                DB::beginTransaction();
                try {
                    // Actualizar la venta
                    $venta->update([
                        'estado' => 'pagado',
                        'estado_pago' => 'completado',
                        'metodo_pago' => $this->nombreMetodoPago($metodoPago),
                        'fecha_pago' => now(),
                    ]);
                    
                    // Limpiar el carrito del usuario
                    Carrito::where('usuario_id', $venta->usuario_id)->delete();
                    
                    // Actualizar inventario (descontar cantidad)
                    foreach ($venta->detalleventas as $detalle) {
                        if ($detalle->producto) {
                            $detalle->producto->decrement('cantidad', $detalle->cantidad);
                        }
                    }
                    
                    DB::commit();
                    
                    Log::info('✅ Venta actualizada a PAGADO:', [
                        'venta_id' => $venta->id,
                        'usuario_id' => $venta->usuario_id,
                        'total' => $venta->total
                    ]);
                    
                    return response()->json([
                        'error' => 0,
                        'status' => 1,
                        'message' => 'Pago procesado correctamente',
                        'messageMostrar' => 0,
                        'messageSistema' => '',
                        'values' => true
                    ], 200);
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error actualizando venta:', ['error' => $e->getMessage()]);
                    
                    return response()->json([
                        'error' => 1,
                        'status' => 0,
                        'message' => 'Error al procesar el pago',
                        'messageMostrar' => 0,
                        'messageSistema' => $e->getMessage(),
                        'values' => false
                    ], 200);
                }
                
            } else {
                Log::warning('❌ Pago no confirmado', ['estado' => $estado, 'venta_id' => $venta->id]);
                
                // Si el pago expiró (estado 3), actualizar la venta
                if ($estado == 3) {
                    $venta->update(['estado' => 'expirado']);
                }
                
                return response()->json([
                    'error' => 0,
                    'status' => 0,
                    'message' => 'Pago no confirmado',
                    'messageMostrar' => 0,
                    'messageSistema' => 'Estado: ' . $estado,
                    'values' => false
                ], 200);
            }

        } catch (\Exception $e) {
            Log::error('❌ ERROR EN CALLBACK:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => 'Error interno',
                'messageMostrar' => 0,
                'messageSistema' => $e->getMessage(),
                'values' => false
            ], 200);
        }
    }
    
    /**
     * Verificar estado de pago por paymentNumber (para el frontend)
     */
    public function verificarPago(Request $request)
    {
        $paymentNumber = $request->input('paymentNumber');
        
        if (!$paymentNumber) {
            return response()->json([
                'success' => false,
                'message' => 'paymentNumber requerido'
            ], 400);
        }
        
        // Primero buscar en cuotas
        $cuota = Cuota::where('payment_number', $paymentNumber)->first();
        if ($cuota) {
            $venta = $cuota->venta;
            return response()->json([
                'success' => true,
                'pagado' => $cuota->estado === 'pagado',
                'estado' => $cuota->estado,
                'venta_id' => $venta->id,
                'tipo_pago' => $venta->tipo_pago,
                'estado_pago' => $venta->estado_pago,
                'cuota_numero' => $cuota->numero_cuota,
                'cuotas_pendientes' => $venta->cuotasPendientes()->count(),
            ]);
        }
        
        $venta = Venta::where('payment_number', $paymentNumber)->first();
        
        if (!$venta) {
            return response()->json([
                'success' => false,
                'pagado' => false,
                'message' => 'Venta no encontrada'
            ]);
        }
        
        // Si es crédito, verificar estado de la primera cuota
        if ($venta->esCredito()) {
            $primeraCuota = $venta->cuotas()->where('numero_cuota', 1)->first();
            return response()->json([
                'success' => true,
                'pagado' => $primeraCuota ? $primeraCuota->estado === 'pagado' : false,
                'estado' => $venta->estado,
                'venta_id' => $venta->id,
                'tipo_pago' => $venta->tipo_pago,
                'estado_pago' => $venta->estado_pago,
                'cuota_numero' => 1,
                'cuotas_pendientes' => $venta->cuotasPendientes()->count(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'pagado' => $venta->estado === 'pagado',
            'estado' => $venta->estado,
            'venta_id' => $venta->id,
            'tipo_pago' => $venta->tipo_pago ?? 'contado',
            'estado_pago' => $venta->estado_pago ?? 'completado',
        ]);
    }

    /**
     * Procesar pago de una cuota (usado por el callback)
     */
    private function procesarPagoCuota(Cuota $cuota, $estado, $metodoPago)
    {
        $venta = $cuota->venta;
        
        Log::info('Procesando pago de cuota:', [
            'cuota_id' => $cuota->id,
            'numero_cuota' => $cuota->numero_cuota,
            'venta_id' => $venta->id,
            'estado_pago' => $estado
        ]);

        // Estado 2 = Pago exitoso
        if ($estado == 2) {
            DB::beginTransaction();
            try {
                // Marcar cuota como pagada
                $cuota->update([
                    'estado' => 'pagado',
                    'fecha_pago' => now(),
                ]);

                // Actualizar estado de la venta
                $venta->actualizarEstadoPago();

                // Si es la primera cuota, limpiar carrito y descontar inventario
                if ($cuota->numero_cuota === 1) {
                    Carrito::where('usuario_id', $venta->usuario_id)->delete();
                    
                    foreach ($venta->detalleventas as $detalle) {
                        if ($detalle->producto) {
                            $detalle->producto->decrement('cantidad', $detalle->cantidad);
                        }
                    }
                }

                // Si todas las cuotas están pagadas, marcar venta como pagada completamente
                if ($venta->estado_pago === 'completado') {
                    $venta->update([
                        'estado' => 'pagado',
                        'metodo_pago' => $this->nombreMetodoPago($metodoPago),
                        'fecha_pago' => now(),
                    ]);
                }

                DB::commit();

                Log::info('✅ Cuota pagada exitosamente:', [
                    'cuota_id' => $cuota->id,
                    'numero_cuota' => $cuota->numero_cuota,
                    'venta_estado_pago' => $venta->estado_pago
                ]);

                return response()->json([
                    'error' => 0,
                    'status' => 1,
                    'message' => 'Cuota pagada correctamente',
                    'messageMostrar' => 0,
                    'messageSistema' => '',
                    'values' => true
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error procesando pago de cuota:', ['error' => $e->getMessage()]);
                
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => 'Error al procesar pago de cuota',
                    'messageMostrar' => 0,
                    'messageSistema' => $e->getMessage(),
                    'values' => false
                ], 200);
            }
        }

        return response()->json([
            'error' => 0,
            'status' => 0,
            'message' => 'Pago de cuota no confirmado',
            'messageMostrar' => 0,
            'messageSistema' => 'Estado: ' . $estado,
            'values' => false
        ], 200);
    }

    /**
     * Generar QR para pagar siguiente cuota pendiente
     */
    public function generarQrCuota(Request $request)
    {
        try {
            $validated = $request->validate([
                'venta_id' => 'required|exists:ventas,id',
                'tcRazonSocial' => 'required|string|max:100',
                'tcCiNit' => 'required|string|max:20',
                'tnTelefono' => 'required|string|min:8|max:15',
                'tcCorreo' => 'required|email|max:100',
            ]);

            $venta = Venta::with('cuotas')->findOrFail($validated['venta_id']);
            
            // Verificar que sea venta a crédito
            if (!$venta->esCredito()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta venta no es a crédito'
                ], 400);
            }

            // Obtener siguiente cuota pendiente
            $cuota = $venta->siguienteCuotaPendiente();
            
            if (!$cuota) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay cuotas pendientes'
                ], 400);
            }

            // Generar QR para esta cuota
            $datosQr = [
                'nombre_cliente' => $validated['tcRazonSocial'],
                'document_id' => preg_replace('/[^0-9]/', '', $validated['tcCiNit']),
                'telefono' => preg_replace('/[^0-9]/', '', $validated['tnTelefono']),
                'email' => $validated['tcCorreo'],
                'numero_pedido' => 'CUOTA_' . $venta->id . '_' . $cuota->numero_cuota . '_' . time(),
                'monto' => floatval($cuota->monto),
                'detalle_pedido' => "Cuota {$cuota->numero_cuota} de 2 - Venta #{$venta->id}",
            ];

            $resultado = $this->pagoFacilService->generarQr($datosQr);

            if ($resultado) {
                // Actualizar cuota con payment_number
                $cuota->update([
                    'payment_number' => $resultado['paymentNumber'],
                    'transaction_id' => $resultado['transactionId'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'qrImage' => $resultado['qrBase64'] ?? $resultado['qrImage'] ?? null,
                        'paymentNumber' => $resultado['paymentNumber'],
                        'transactionId' => $resultado['transactionId'] ?? null,
                        'amount' => $cuota->monto,
                        'cuota_numero' => $cuota->numero_cuota,
                        'fecha_vencimiento' => $cuota->fecha_vencimiento->format('Y-m-d'),
                        'expirationDate' => $resultado['expirationDate'] ?? null,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el QR para la cuota'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error generando QR de cuota:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cuotas pendientes del usuario actual
     */
    public function misCuotasPendientes()
    {
        $usuarioId = Auth::id();
        
        if (!$usuarioId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $ventasConCuotas = Venta::where('usuario_id', $usuarioId)
            ->where('tipo_pago', 'credito')
            ->whereIn('estado_pago', ['pendiente', 'parcial'])
            ->with(['cuotas' => function($q) {
                $q->where('estado', 'pendiente')->orderBy('numero_cuota');
            }, 'detalleventas.producto'])
            ->get();

        $cuotasPendientes = [];
        foreach ($ventasConCuotas as $venta) {
            foreach ($venta->cuotas as $cuota) {
                $cuotasPendientes[] = [
                    'venta_id' => $venta->id,
                    'cuota_id' => $cuota->id,
                    'numero_cuota' => $cuota->numero_cuota,
                    'monto' => $cuota->monto,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento->format('Y-m-d'),
                    'esta_vencida' => $cuota->estaVencida(),
                    'total_venta' => $venta->total,
                    'productos' => $venta->detalleventas->map(fn($d) => [
                        'nombre' => $d->producto->nombre ?? 'Producto',
                        'cantidad' => $d->cantidad,
                    ]),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'cuotas' => $cuotasPendientes
        ]);
    }
}
