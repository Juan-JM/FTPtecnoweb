# Test PagoFacil API - Comandos cURL

## 1. Autenticación (Login)

```bash
curl -X POST "https://masterqr.pagofacil.com.bo/api/services/v2/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "tcTokenService: 51247fae280c20410824977b0781453df59fad5b23bf2a0d14e884482f91e09078dbe5966e0b970ba696ec4caf9aa5661802935f86717c481f1670e63f35d504a62547a9de71bfc76be2c2ae01039ebcb0f74a96f0f1f56542c8b51ef7a2a6da9ea16f23e52ecc4485b69640297a5ec6a701498d2f0e1b4e7f4b7803bf5c2eba" \
  -H "tcTokenSecret: 0C351C6679844041AA31AF9C"
```

**PowerShell:**
```powershell
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
    "tcTokenService" = "51247fae280c20410824f0f03cb5edce77b35cf5423e39a450bc6e8e6e72ac4b"
    "tcTokenSecret" = "0C351C6679844041AA31AF9C"
}
Invoke-RestMethod -Uri "https://masterqr.pagofacil.com.bo/api/services/v2/login" -Method POST -Headers $headers
```

---

## 2. Generar QR (con token obtenido del paso 1)

**Reemplaza `TU_ACCESS_TOKEN` con el token que obtuviste en el paso 1**

### Opción A: Formato con taPedidoDetalle como ARRAY

```bash
curl -X POST "https://masterqr.pagofacil.com.bo/api/services/v2/generate-qr" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TU_ACCESS_TOKEN" \
  -d '{
    "tcCommerceID": "47",
    "tnMoneda": 2,
    "tnTelefono": 71064272,
    "tcCorreo": "juan@gmail.com",
    "tcNombreUsuario": "Juan Noe Jarpa",
    "tnCiNit": 8214586,
    "tcNroPago": "TEST-001",
    "tnMonto": 0.1,
    "tcUrlCallBack": "https://tecnoweb.org.bo/inf513/grupo20sa/proyecto2/public/pagofacil/callback",
    "tcUrlReturn": "https://tecnoweb.org.bo/inf513/grupo20sa/proyecto2/public/pagos",
    "taPedidoDetalle": [
      {
        "Serial": 1,
        "Producto": "Producto de prueba",
        "Cantidad": 1,
        "Precio": 0.1,
        "Descuento": 0,
        "Total": 0.1
      }
    ]
  }'
```

### Opción B: Formato con campos camelCase

```bash
curl -X POST "https://masterqr.pagofacil.com.bo/api/services/v2/generate-qr" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TU_ACCESS_TOKEN" \
  -d '{
    "paymentMethod": 4,
    "clientName": "Juan Noe Jarpa",
    "documentType": 1,
    "documentId": "8214586",
    "phoneNumber": "71064272",
    "email": "juan@gmail.com",
    "paymentNumber": "TEST-002",
    "amount": 0.1,
    "currency": 2,
    "clientCode": "00001",
    "callbackUrl": "https://tecnoweb.org.bo/inf513/grupo20sa/proyecto2/public/pagofacil/callback",
    "orderDetail": [
      {
        "serial": 1,
        "product": "Producto de prueba",
        "quantity": 1,
        "price": 0.1,
        "discount": 0,
        "total": 0.1
      }
    ]
  }'
```

### Opción C: Formato minimalista

```bash
curl -X POST "https://masterqr.pagofacil.com.bo/api/services/v2/generate-qr" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TU_ACCESS_TOKEN" \
  -d '{
    "amount": 0.1,
    "currency": 2,
    "paymentNumber": "TEST-003",
    "clientName": "Test",
    "email": "test@test.com",
    "callbackUrl": "https://tecnoweb.org.bo/inf513/grupo20sa/proyecto2/public/pagofacil/callback"
  }'
```

---

## PowerShell - Script completo de prueba

```powershell
# Paso 1: Autenticación
$loginHeaders = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
    "tcTokenService" = "51247fae280c20410824f0f03cb5edce77b35cf5423e39a450bc6e8e6e72ac4b"
    "tcTokenSecret" = "0C351C6679844041AA31AF9C"
}

Write-Host "=== PASO 1: Autenticación ===" -ForegroundColor Cyan
$loginResponse = Invoke-RestMethod -Uri "https://masterqr.pagofacil.com.bo/api/services/v2/login" -Method POST -Headers $loginHeaders
$loginResponse | ConvertTo-Json -Depth 10

# Obtener token
$token = $loginResponse.values.accessToken
Write-Host "`nToken obtenido: $($token.Substring(0, 30))..." -ForegroundColor Green

# Paso 2: Generar QR
$qrHeaders = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
    "Authorization" = "Bearer $token"
}

# Opción A: con taPedidoDetalle array
$bodyA = @{
    tcCommerceID = "47"
    tnMoneda = 2
    tnTelefono = 71064272
    tcCorreo = "juan@gmail.com"
    tcNombreUsuario = "Juan Test"
    tnCiNit = 8214586
    tcNroPago = "TEST-$(Get-Date -Format 'yyyyMMddHHmmss')"
    tnMonto = 0.1
    tcUrlCallBack = "https://tecnoweb.org.bo/inf513/grupo20sa/proyecto2/public/pagofacil/callback"
    tcUrlReturn = "https://tecnoweb.org.bo/inf513/grupo20sa/proyecto2/public/pagos"
    taPedidoDetalle = @(
        @{
            Serial = 1
            Producto = "Producto Test"
            Cantidad = 1
            Precio = 0.1
            Descuento = 0
            Total = 0.1
        }
    )
} | ConvertTo-Json -Depth 10

Write-Host "`n=== PASO 2A: Generar QR (formato taPedidoDetalle) ===" -ForegroundColor Cyan
Write-Host "Body enviado:" -ForegroundColor Yellow
$bodyA
try {
    $qrResponse = Invoke-RestMethod -Uri "https://masterqr.pagofacil.com.bo/api/services/v2/generate-qr" -Method POST -Headers $qrHeaders -Body $bodyA
    $qrResponse | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    $_.Exception.Response
}

# Opción B: formato camelCase
$bodyB = @{
    paymentMethod = 4
    clientName = "Juan Test"
    documentType = 1
    documentId = "8214586"
    phoneNumber = "71064272"
    email = "juan@gmail.com"
    paymentNumber = "TEST2-$(Get-Date -Format 'yyyyMMddHHmmss')"
    amount = 0.1
    currency = 2
    clientCode = "00001"
    callbackUrl = "https://tecnoweb.org.bo/inf513/grupo20sa/proyecto2/public/pagofacil/callback"
    orderDetail = @(
        @{
            serial = 1
            product = "Test"
            quantity = 1
            price = 0.1
            discount = 0
            total = 0.1
        }
    )
} | ConvertTo-Json -Depth 10

Write-Host "`n=== PASO 2B: Generar QR (formato camelCase) ===" -ForegroundColor Cyan
Write-Host "Body enviado:" -ForegroundColor Yellow
$bodyB
try {
    $qrResponse2 = Invoke-RestMethod -Uri "https://masterqr.pagofacil.com.bo/api/services/v2/generate-qr" -Method POST -Headers $qrHeaders -Body $bodyB
    $qrResponse2 | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $reader.BaseStream.Position = 0
        $reader.ReadToEnd()
    }
}
```

---

## Notas

- El monto mínimo es **0.1 BOB**
- `tnMoneda = 2` es Bolivianos (BOB)
- El error `array_map()` indica que `taPedidoDetalle` o `orderDetail` debe ser un **array**
- Prueba las 3 opciones para ver cuál funciona con la API actual
