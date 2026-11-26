<?php

use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\HabitacionController as AdminHabitacionController;
use App\Http\Controllers\Api\V1\Admin\ImagenHabitacionController;
use App\Http\Controllers\Api\V1\Admin\NotificacionController;
use App\Http\Controllers\Api\V1\Admin\ReservaController as AdminReservaController;
use App\Http\Controllers\Api\V1\Admin\UsuarioController;
use App\Http\Controllers\Api\V1\Admin\ZonaController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogoController;
use App\Http\Controllers\Api\V1\PagoController;
use App\Http\Controllers\Api\V1\PerfilController;
use App\Http\Controllers\Api\V1\ReservaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 (app móvil)
|--------------------------------------------------------------------------
| Prefijo: /api/v1. Autenticación con Laravel Sanctum: enviar
| "Authorization: Bearer {token}" y "Accept: application/json".
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Públicas ─────────────────────────────────────────────
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot');
    });

    Route::get('/portada', [CatalogoController::class, 'portada'])->name('portada');
    Route::get('/zonas', [CatalogoController::class, 'zonas'])->name('zonas.index');
    Route::get('/habitaciones', [CatalogoController::class, 'habitaciones'])->name('habitaciones.index');
    Route::get('/habitaciones/{habitacion}', [CatalogoController::class, 'habitacion'])->name('habitaciones.show');

    // ── Usuario autenticado ──────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
        Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])->name('perfil.password');
        Route::delete('/perfil', [PerfilController::class, 'destroy'])->name('perfil.destroy');

        Route::get('/reservas', [ReservaController::class, 'index'])->name('reservas.index');
        Route::post('/reservas', [ReservaController::class, 'store'])->name('reservas.store');
        Route::get('/reservas/{reserva}', [ReservaController::class, 'show'])->name('reservas.show');
        Route::post('/reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');

        Route::post('/reservas/{reserva}/pago', [PagoController::class, 'store'])->name('pagos.store');
        Route::get('/reservas/{reserva}/factura', [PagoController::class, 'factura'])->name('pagos.factura');

        // ── Administración (rol Administrador) ───────────────
        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', DashboardController::class)->name('dashboard');

            Route::apiResource('zonas', ZonaController::class);
            Route::apiResource('habitaciones', AdminHabitacionController::class)
                ->parameters(['habitaciones' => 'habitacion']);

            Route::post('/habitaciones/{habitacion}/imagenes', [ImagenHabitacionController::class, 'store'])
                ->name('habitaciones.imagenes.store');
            Route::patch('/imagenes/{imagen}/principal', [ImagenHabitacionController::class, 'setPrincipal'])
                ->name('imagenes.principal');
            Route::delete('/imagenes/{imagen}', [ImagenHabitacionController::class, 'destroy'])
                ->name('imagenes.destroy');

            Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');

            Route::get('/reservas', [AdminReservaController::class, 'index'])->name('reservas.index');
            Route::get('/reservas/{reserva}', [AdminReservaController::class, 'show'])->name('reservas.show');
            Route::patch('/reservas/{reserva}/estado', [AdminReservaController::class, 'cambiarEstado'])
                ->name('reservas.cambiarEstado');

            // marcar-todas va ANTES de {notificacion} para que no lo capture el parámetro
            Route::patch('/notificaciones/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas'])
                ->name('notificaciones.marcarTodas');
            Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
            Route::patch('/notificaciones/{notificacion}/leida', [NotificacionController::class, 'marcarLeida'])
                ->name('notificaciones.marcarLeida');
        });
    });
});
