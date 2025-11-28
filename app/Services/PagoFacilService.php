<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PagoFacilService
{
    private string $baseUrl;
    private string $tokenService;
    private string $tokenSecret;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->baseUrl = config('services.pagofacil.base_url', 'https://masterqr.pagofacil.com.bo/api/services/v2');
        $this->tokenService = config('services.pagofacil.token_service');
        $this->tokenSecret = config('services.pagofacil.token_secret');
    }

    /**
     * Autenticar y obtener access token
     */
    public function authenticate(): ?string
    {
        try {
            Log::info('PagoFacil: Iniciando autenticación');

            $response = Http::timeout(10)->withHeaders([
                'tcTokenService' => $this->tokenService,
                'tcTokenSecret' => $this->tokenSecret,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/login');

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['error']) && $data['error'] === 0 && isset($data['values']['accessToken'])) {
                    $this->accessToken = $data['values']['accessToken'];
                    Log::info('PagoFacil: Autenticación exitosa');
                    return $this->accessToken;
                }
            }
            
            Log::error('PagoFacil: Error en autenticación', ['status' => $response->status()]);
            return null;
        } catch (Exception $e) {
            Log::error('PagoFacil: Excepción en autenticación', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Alias para mantener compatibilidad
     */
    public function autenticar(): ?string
    {
        return $this->authenticate();
    }

    /**
     * Generar QR de pago
     */
    public function generateQr(array $data): ?array
    {
        if (!$this->accessToken && !$this->authenticate()) {
            throw new Exception('No se pudo autenticar con PagoFácil');
        }

        try {
            // URL de callback para notificaciones - usar URL real del servidor
            // La ruta /api/ no tiene protección CSRF, ideal para callbacks externos
            $callbackUrl = 'https://tecnoweb.org.bo/inf513/grupo20sa/proyecto2/public/api/notificaciones-pagofacil';
            
            // Generar número de pago único
            $paymentNumber = 'ORD' . time() . rand(1000, 9999);
            
            // Formato correcto según documentación de PagoFacil API v2
            $requestData = [
                'paymentMethod' => 4, // QR
                'clientName' => $data['nombre_cliente'] ?? $data['client_name'] ?? 'Cliente',
                'documentType' => 1, // CI
                'documentId' => strval($data['document_id'] ?? '0'),
                'phoneNumber' => strval($data['telefono'] ?? $data['phone_number'] ?? '70000000'),
                'email' => $data['email'] ?? 'cliente@example.com',
                'paymentNumber' => $paymentNumber,
                'amount' => 0.1, //floatval($data['monto'] ?? $data['amount'] ?? 0.1),
                'currency' => 2, // BOB
                'clientCode' => '11001',
                'callbackUrl' => $callbackUrl,
                'orderDetail' => [
                    [
                        'serial' => 1,
                        'product' => substr($data['detalle_pedido'] ?? $data['order_detail'] ?? 'Compra en TecnoWeb', 0, 100),
                        'quantity' => 1,
                        'price' => 0.1,//floatval($data['monto'] ?? $data['amount'] ?? 0.1),
                        'discount' => 0,
                        'total' => 0.1,//floatval($data['monto'] ?? $data['amount'] ?? 0.1)
                    ]
                ]
            ];

            Log::info('PagoFacil: Generando QR', ['data' => $requestData]);
            
            $response = Http::timeout(15)
                ->withToken($this->accessToken)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . '/generate-qr', $requestData);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('PagoFacil: Respuesta recibida', ['result' => $result]);
                
                if (isset($result['error']) && $result['error'] === 0) {
                    Log::info('PagoFacil: QR generado exitosamente');
                    
                    // Incluir nuestro paymentNumber en la respuesta
                    $values = $result['values'] ?? $result;
                    $values['paymentNumber'] = $paymentNumber; // El que nosotros enviamos
                    
                    Log::info('PagoFacil: paymentNumber guardado:', ['paymentNumber' => $paymentNumber]);
                    
                    return $values;
                }
            }
            
            Log::error('PagoFacil: Error generando QR', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;

        } catch (Exception $e) {
            Log::error('PagoFacil: Excepción generando QR', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Alias para mantener compatibilidad
     */
    public function generarQr(array $data): ?array
    {
        return $this->generateQr($data);
    }

    /**
     * Consultar estado de transacción
     */
    public function queryTransaction(string $transactionId): ?array
    {
        if (!$this->accessToken && !$this->authenticate()) {
            throw new Exception('No se pudo autenticar con PagoFácil');
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->accessToken)
                ->post("{$this->baseUrl}/query-transaction", [
                    'pagofacilTransactionId' => $transactionId,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['error']) && $result['error'] === 0) {
                    return $result['values'];
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error('PagoFacil: Error consultando transacción', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Alias para mantener compatibilidad
     */
    public function consultarTransaccion(string $idTransaccion): ?array
    {
        return $this->queryTransaction($idTransaccion);
    }
}