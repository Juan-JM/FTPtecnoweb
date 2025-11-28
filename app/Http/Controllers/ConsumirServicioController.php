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
                'tnTipoServicio' => 'nullable|numeric'
            ]);
            
            Log::info('Iniciando proceso de pago PagoFacil', ['datos_validados' => $validatedData]);

            // Preparar datos para el servicio
            $datosQr = [
                'nombre_cliente' => $validatedData['tcRazonSocial'],
                'document_id' => preg_replace('/[^0-9]/', '', $validatedData['tcCiNit']),
                'telefono' => preg_replace('/[^0-9]/', '', $validatedData['tnTelefono']),
                'email' => $validatedData['tcCorreo'],
                'numero_pedido' => 'TECNO_' . time() . '_' . rand(1000, 9999),
                'monto' => floatval($validatedData['tnTotal']),
                'detalle_pedido' => $validatedData['taPedidoDetalle'] ?? 'Compra en TecnoWeb',
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
                    'qrImage_exists' => !empty($qrImage)
                ]);
                
                // Crear venta en estado PENDIENTE
                $usuarioId = Auth::id();
                Log::info('Usuario autenticado:', ['usuario_id' => $usuarioId]);
                
                $ventaCreada = null;
                if ($usuarioId && $paymentNumber) {
                    $ventaCreada = $this->crearVentaPendiente($usuarioId, $paymentNumber, $transactionId, floatval($validatedData['tnTotal']));
                    Log::info('Resultado de crear venta:', ['venta' => $ventaCreada ? $ventaCreada->id : 'NULL']);
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
                        'amount' => $datosQr['monto'],
                        'expirationDate' => $resultado['expirationDate'] ?? null,
                        'ventaCreada' => $ventaCreada ? $ventaCreada->id : null
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
    private function crearVentaPendiente($usuarioId, $paymentNumber, $transactionId, $total)
    {
        try {
            Log::info('=== INICIANDO crearVentaPendiente ===', [
                'usuarioId' => $usuarioId,
                'paymentNumber' => $paymentNumber,
                'transactionId' => $transactionId,
                'total' => $total
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

            DB::commit();
            
            Log::info('✅ Venta pendiente creada exitosamente:', [
                'venta_id' => $venta->id,
                'payment_number' => $paymentNumber,
                'transaction_id' => $transactionId
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

            // Buscar la venta por payment_number
            $venta = Venta::where('payment_number', $pedidoId)->first();
            
            Log::info('Búsqueda venta:', [
                'pedidoId' => $pedidoId,
                'encontrada' => $venta ? 'SÍ' : 'NO',
                'venta_id' => $venta->id ?? null,
                'estado_actual' => $venta->estado ?? null
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

            // Estado 2 = Pago exitoso
            if ($estado == 2) {
                Log::info('✅ PAGO CONFIRMADO - Actualizando venta:', ['venta_id' => $venta->id]);
                
                DB::beginTransaction();
                try {
                    // Actualizar la venta
                    $venta->update([
                        'estado' => 'pagado',
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
        
        $venta = Venta::where('payment_number', $paymentNumber)->first();
        
        if (!$venta) {
            return response()->json([
                'success' => false,
                'pagado' => false,
                'message' => 'Venta no encontrada'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'pagado' => $venta->estado === 'pagado',
            'estado' => $venta->estado,
            'venta_id' => $venta->id
        ]);
    }
}
