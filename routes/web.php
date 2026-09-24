<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImagenHabitacionController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ZonaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reservas (cualquier usuario autenticado)
    Route::post('/reservas', [ReservaController::class, 'store'])->name('reservas.store');
    Route::get('/mis-reservas', [ReservaController::class, 'misReservas'])->name('reservas.mias');
    Route::post('/reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');

    // Pago simulado de una reserva
    Route::get('/reservas/{reserva}/pago', [PagoController::class, 'create'])->name('pagos.create');
    Route::post('/reservas/{reserva}/pago', [PagoController::class, 'store'])->name('pagos.store');
    Route::get('/reservas/{reserva}/factura', [PagoController::class, 'factura'])->name('pagos.factura');
});

// Vista pública de detalle de habitación
Route::get('/habitaciones/{habitacion}', [HabitacionController::class, 'show'])
     ->name('habitaciones.show');

// Panel de administración — requiere auth + rol Administrador
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
    Route::resource('zonas', ZonaController::class);
    Route::resource('habitaciones', HabitacionController::class)
         ->except(['show'])
         ->parameters(['habitaciones' => 'habitacion']);

    // Imágenes de habitación
    Route::post('/habitaciones/{habitacion}/imagenes', [ImagenHabitacionController::class, 'store'])
         ->name('habitaciones.imagenes.store');
    Route::patch('/imagenes/{imagen}/principal', [ImagenHabitacionController::class, 'setPrincipal'])
         ->name('imagenes.principal');
    Route::delete('/imagenes/{imagen}', [ImagenHabitacionController::class, 'destroy'])
         ->name('imagenes.destroy');

    // Usuarios (admin, solo lectura)
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');

    // Reservas (admin)
    Route::get('/reservas', [ReservaController::class, 'index'])->name('reservas.index');
    Route::patch('/reservas/{reserva}/estado', [ReservaController::class, 'cambiarEstado'])->name('reservas.cambiarEstado');

    // Notificaciones (admin)
    // La ruta marcarTodas debe ir ANTES de marcarLeida para evitar que {notificacion} capture 'marcar-todas'
    Route::patch('/notificaciones/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.marcarTodas');
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::patch('/notificaciones/{notificacion}/leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.marcarLeida');
});

require __DIR__.'/auth.php';
