<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DetalleCompraController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\DetalleVentaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ConsumirServicioController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\ContadorController;
use App\Http\Controllers\CarritoController;

// Aplicar middleware de contador de visitas a todas las rutas
Route::middleware([\App\Http\Middleware\ContadorDeVisitas::class])->group(function () {

    Route::get('/menu', [MenuController::class, 'index']);

    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ])->group(function () {
        Route::get('/dashboard', function () {
            $user = Auth::user();

            // Redirigir según el rol del usuario
            switch ($user->rol) {
                case 'admin':
                    return app(DashboardController::class)->index();
                case 'proveedor':
                    return redirect()->route('proveedor.dashboard');
                case 'cliente':
                default:
                    return app(DashboardController::class)->index();
            }
        })->name('dashboard');
    });

    // NUEVAS RUTAS PARA CATÁLOGO Y CARRITO
    Route::middleware('auth:sanctum')->group(function () {
        // Catálogo público
        Route::get('/catalogo', [ProductoController::class, 'catalogo'])->name('catalogo');

        // Carrito (solo para clientes)
        Route::middleware('role:cliente')->group(function () {
            Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
            Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
            Route::put('/carrito/{id}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
            Route::delete('/carrito/{id}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
            Route::delete('/carrito/limpiar', [CarritoController::class, 'limpiar'])->name('carrito.limpiar');
        });

        // API para contador de carrito
        Route::get('/carrito/contar', [CarritoController::class, 'contarItems'])->name('carrito.contar');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/ventas/cliente', [VentaController::class, 'crearVentaCliente'])->name('ventas.cliente.crear');
        Route::get('/ventas/cliente/{id}/detalle', [DetalleVentaController::class, 'mostrarDetalleVentaCliente'])->name('ventas.cliente.detalle.mostrar');
        Route::post('/ventas/cliente/{id}/detalle', [DetalleVentaController::class, 'agregarProductoVentaCliente'])->name('ventas.cliente.detalle.agregar');
        Route::get('/products', [ProductoController::class, 'getAllProducts']);
        Route::post('/pagos/crear', [PagoController::class, 'crear'])->name('pagos.crear');
    });

    // Rutas de administración con autenticación y rol
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/admin/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios');
        Route::post('/admin/usuarios/{id}/asignar-rol', [UsuarioController::class, 'asignarRol'])->name('admin.usuarios.asignar-rol');
        Route::get('/admin/categorias', [CategoriaController::class, 'index'])->name('admin.categorias');
        Route::get('/admin/ventas', [VentaController::class, 'index'])->name('admin.ventas');
        Route::get('/admin/pagos', [PagoController::class, 'index'])->name('admin.pagos');
        Route::get('/admin/productos', [ProductoController::class, 'index'])->name('admin.productos');
        Route::get('/admin/promocion', [PromocionController::class, 'index'])->name('admin.promociones');
        Route::get('/admin/inventarios', [InventarioController::class, 'index'])->name('admin.inventarios');
        Route::get('/admin/compras', [CompraController::class, 'index'])->name('admin.compras');
    });

    // CRUD Categorías
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::post('/categorias', [CategoriaController::class, 'store'])->name('admin.categorias.store');
        Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->name('admin.categorias.update');
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('admin.categorias.destroy');
    });

    // CRUD Compras
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::post('/compras', [CompraController::class, 'store'])->name('admin.compras.store');
        Route::put('/compras/{id}', [CompraController::class, 'update'])->name('admin.compras.update');
        Route::delete('/compras/{id}', [CompraController::class, 'destroy'])->name('admin.compras.destroy');
    });

    // CRUD Ventas
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::post('/ventas', [VentaController::class, 'store'])->name('admin.ventas.store');
        Route::put('/ventas/{id}', [VentaController::class, 'update'])->name('admin.ventas.update');
        Route::delete('/ventas/{id}', [VentaController::class, 'destroy'])->name('admin.ventas.destroy');
        Route::get('/ventas/{id}', [VentaController::class, 'show'])->name('admin.ventas.show');
    });

    // Detalles de ventas y compras
    Route::prefix('admin')->group(function () {
        Route::get('/detalleventas/{id}', [DetalleVentaController::class, 'show'])->name('admin.detalleventas');
        Route::post('/detalleventas/{venta_id}', [DetalleVentaController::class, 'store']);
        Route::get('/detallecompras/{id}', [DetalleCompraController::class, 'show'])->name('admin.detallecompras');
        Route::post('/detallecompras/{compra_id}', [DetalleCompraController::class, 'store']);
    });

    // CRUD Pagos
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::post('/pagos', [PagoController::class, 'store'])->name('admin.pagos.store');
        Route::put('/pagos/{id}', [PagoController::class, 'update'])->name('admin.pagos.update');
        Route::delete('/pagos/{id}', [PagoController::class, 'destroy'])->name('admin.pagos.destroy');
    });

    // CRUD Productos (ACTUALIZADO PARA MANEJAR IMÁGENES)
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::post('/productos', [ProductoController::class, 'store'])->name('admin.productos.store');
        Route::put('/productos/{id}', [ProductoController::class, 'update'])->name('admin.productos.update');
        Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->name('admin.productos.destroy');
    });

    // CRUD Promociones
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::post('/promociones', [PromocionController::class, 'store'])->name('admin.promociones.store');
        Route::put('/promociones/{id}', [PromocionController::class, 'update'])->name('admin.promociones.update');
        Route::delete('/promociones/{id}', [PromocionController::class, 'destroy'])->name('admin.promociones.destroy');
    });

    // CRUD Inventarios
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::post('/inventarios', [InventarioController::class, 'store'])->name('admin.inventarios.store');
        Route::put('/inventarios/{id}', [InventarioController::class, 'update'])->name('admin.inventarios.update');
        Route::delete('/inventarios/{id}', [InventarioController::class, 'destroy'])->name('admin.inventarios.destroy');
        Route::get('/inventarios/resumen', [InventarioController::class, 'resumen'])->name('admin.inventarios.resumen');
    });

    // CRUD Menús dinámicos
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::post('/menus', [MenuController::class, 'store'])->name('admin.menus.store');
        Route::put('/menus/{id}', [MenuController::class, 'update'])->name('admin.menus.update');
        Route::delete('/menus/{id}', [MenuController::class, 'destroy'])->name('admin.menus.destroy');
    });

    // CRUD Usuarios
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('admin.usuarios.store');
        Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('admin.usuarios.destroy');
    });

    Route::get('/pagos', [CarritoController::class, 'pago'])->name('pagos.index');

    // Servicios de pago
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/recolectar-datos', [ConsumirServicioController::class, 'recolectarDatos'])->name('recolectar.datos');
        Route::post('/consultar', [ConsumirServicioController::class, 'consultarEstado'])->name('consultar.estado');
        Route::post('/verificar-pago', [ConsumirServicioController::class, 'verificarPago'])->name('verificar.pago');
        Route::post('/urlcallback', [ConsumirServicioController::class, 'urlCallback'])->name('url.callback');
    });

    // Rutas para PROVEEDORES (acceso a compras y catálogo)
    Route::middleware(['auth', 'role:proveedor'])->group(function () {
        // Dashboard del proveedor (mismo que admin pero limitado)
        Route::get('/proveedor/dashboard', [DashboardController::class, 'proveedorIndex'])->name('proveedor.dashboard');

        // Gestión de compras para proveedores
        Route::get('/proveedor/compras', [CompraController::class, 'proveedorIndex'])->name('proveedor.compras');
        Route::post('/proveedor/compras', [CompraController::class, 'proveedorStore'])->name('proveedor.compras.store');
        Route::put('/proveedor/compras/{id}', [CompraController::class, 'proveedorUpdate'])->name('proveedor.compras.update');
        Route::delete('/proveedor/compras/{id}', [CompraController::class, 'proveedorDestroy'])->name('proveedor.compras.destroy');

        // Gestión de inventarios para proveedores
        Route::get('/proveedor/inventarios', [InventarioController::class, 'proveedorIndex'])->name('proveedor.inventarios');
        Route::post('/proveedor/inventarios', [InventarioController::class, 'proveedorStore'])->name('proveedor.inventarios.store');
        Route::put('/proveedor/inventarios/{id}', [InventarioController::class, 'proveedorUpdate'])->name('proveedor.inventarios.update');
        Route::delete('/proveedor/inventarios/{id}', [InventarioController::class, 'proveedorDestroy'])->name('proveedor.inventarios.destroy');


        // Detalles de compras para proveedores
        Route::get('/proveedor/detallecompras/{id}', [DetalleCompraController::class, 'proveedorShow'])->name('proveedor.detallecompras');
        Route::post('/proveedor/detallecompras/{compra_id}', [DetalleCompraController::class, 'proveedorStore'])->name('proveedor.detallecompras.store');
    });

    // También permitir a proveedores ver el catálogo
    Route::middleware(['auth', 'role:proveedor,cliente'])->group(function () {
        Route::get('/catalogo', [ProductoController::class, 'catalogo'])->name('catalogo');
    });

    // Estadísticas
    Route::get('/estadisticas/ventas-totales', [EstadisticasController::class, 'ventasTotales']);
    Route::get('/estadisticas/ventas-por-producto', [EstadisticasController::class, 'ventasPorProducto']);
    Route::get('/estadisticas/clientes-nuevos', [EstadisticasController::class, 'clientesNuevos']);
    Route::get('/estadisticas/ventas-por-periodo', [EstadisticasController::class, 'ventasPorPeriodo']);
    Route::get('/estadisticas', [EstadisticasController::class, 'index'])->name('estadisticas.index');

    // API para contador de visitas
    Route::get('/api/contador-visitas', [ContadorController::class, 'obtenerContadores']);
});

// API de productos (fuera del middleware de contador para evitar contar estas llamadas)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/api/productos', [ProductoController::class, 'apiIndex']);
});

// PagoFacil callbacks (públicos - no requieren autenticación)
Route::post('/pagofacil/callback', [ConsumirServicioController::class, 'urlCallback'])->name('pagofacil.callback');
Route::post('/notificaciones-pagofacil', [ConsumirServicioController::class, 'urlCallback'])->name('pagofacil.notificaciones');