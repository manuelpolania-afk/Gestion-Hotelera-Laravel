# Rutas

Todas las rutas viven en `routes/web.php` (dominio de la app) y `routes/auth.php` (scaffolding de Breeze, requerido al final de `web.php`).

## Públicas (sin autenticación)

| Método | URI | Controlador@método | Nombre |
|---|---|---|---|
| GET | `/` | `HomeController@index` | `home` |
| GET | `/habitaciones/{habitacion}` | `HabitacionController@show` | `habitaciones.show` |

`HomeController@index` acepta un query param opcional `zona_id` para filtrar el catálogo por zona.

## Autenticadas (middleware `auth`)

| Método | URI | Controlador@método | Nombre |
|---|---|---|---|
| GET | `/dashboard` | Closure → `view('dashboard')` | `dashboard` (además requiere `verified`) |
| GET | `/profile` | `ProfileController@edit` | `profile.edit` |
| PATCH | `/profile` | `ProfileController@update` | `profile.update` |
| DELETE | `/profile` | `ProfileController@destroy` | `profile.destroy` |
| POST | `/reservas` | `ReservaController@store` | `reservas.store` |
| GET | `/mis-reservas` | `ReservaController@misReservas` | `reservas.mias` |
| POST | `/reservas/{reserva}/cancelar` | `ReservaController@cancelar` | `reservas.cancelar` |
| GET | `/reservas/{reserva}/pago` | `PagoController@create` | `pagos.create` |
| POST | `/reservas/{reserva}/pago` | `PagoController@store` | `pagos.store` |

Cualquier usuario autenticado (rol `Cliente` o `Administrador`) puede reservar, cancelar y pagar sus propias reservas. La autorización de "propietario" se valida dentro de cada controlador comparando `detalle.user_id` con `auth()->id()`.

## Panel de administración (middleware `auth` + `admin`, prefijo `admin`, nombre `admin.*`)

Requiere `EnsureUserIsAdmin` — ver [ROLES_Y_PERMISOS.md](ROLES_Y_PERMISOS.md).

| Método | URI | Controlador@método | Nombre |
|---|---|---|---|
| GET | `admin/` | `AdminDashboardController@index` | `admin.index` |
| GET | `admin/zonas` | `ZonaController@index` | `admin.zonas.index` |
| GET | `admin/zonas/create` | `ZonaController@create` | `admin.zonas.create` |
| POST | `admin/zonas` | `ZonaController@store` | `admin.zonas.store` |
| GET | `admin/zonas/{zona}/edit` | `ZonaController@edit` | `admin.zonas.edit` |
| PUT/PATCH | `admin/zonas/{zona}` | `ZonaController@update` | `admin.zonas.update` |
| DELETE | `admin/zonas/{zona}` | `ZonaController@destroy` | `admin.zonas.destroy` |
| GET | `admin/habitaciones` | `HabitacionController@index` | `admin.habitaciones.index` |
| GET | `admin/habitaciones/create` | `HabitacionController@create` | `admin.habitaciones.create` |
| POST | `admin/habitaciones` | `HabitacionController@store` | `admin.habitaciones.store` |
| GET | `admin/habitaciones/{habitacion}/edit` | `HabitacionController@edit` | `admin.habitaciones.edit` |
| PUT/PATCH | `admin/habitaciones/{habitacion}` | `HabitacionController@update` | `admin.habitaciones.update` |
| DELETE | `admin/habitaciones/{habitacion}` | `HabitacionController@destroy` | `admin.habitaciones.destroy` |
| POST | `admin/habitaciones/{habitacion}/imagenes` | `ImagenHabitacionController@store` | `admin.habitaciones.imagenes.store` |
| PATCH | `admin/imagenes/{imagen}/principal` | `ImagenHabitacionController@setPrincipal` | `admin.imagenes.principal` |
| DELETE | `admin/imagenes/{imagen}` | `ImagenHabitacionController@destroy` | `admin.imagenes.destroy` |
| GET | `admin/usuarios` | `Admin\UsuarioController@index` | `admin.usuarios.index` |
| GET | `admin/reservas` | `ReservaController@index` | `admin.reservas.index` |
| PATCH | `admin/reservas/{reserva}/estado` | `ReservaController@cambiarEstado` | `admin.reservas.cambiarEstado` |
| PATCH | `admin/notificaciones/marcar-todas` | `NotificacionController@marcarTodasLeidas` | `admin.notificaciones.marcarTodas` |
| GET | `admin/notificaciones` | `NotificacionController@index` | `admin.notificaciones.index` |
| PATCH | `admin/notificaciones/{notificacion}/leida` | `NotificacionController@marcarLeida` | `admin.notificaciones.marcarLeida` |

Notas:
- `Route::resource('habitaciones', ...)` excluye `show` (esa vista es pública, fuera del grupo admin) y usa el parámetro `{habitacion}` en singular gracias a `->parameters(['habitaciones' => 'habitacion'])`.
- La ruta `admin/notificaciones/marcar-todas` está declarada **antes** que `admin/notificaciones/{notificacion}/leida` a propósito, para que Laravel no intente interpretar `marcar-todas` como un `{notificacion}` numérico.
- El módulo de usuarios (`admin.usuarios.index`) es **solo lectura** por ahora: no hay crear/editar/eliminar usuarios desde el panel.

## Autenticación (`routes/auth.php`)

### Invitados (middleware `guest`)

| Método | URI | Controlador@método | Nombre |
|---|---|---|---|
| GET | `register` | `RegisteredUserController@create` | `register` |
| POST | `register` | `RegisteredUserController@store` | — |
| GET | `login` | `AuthenticatedSessionController@create` | `login` |
| POST | `login` | `AuthenticatedSessionController@store` | — |
| GET | `forgot-password` | `PasswordResetLinkController@create` | `password.request` |
| POST | `forgot-password` | `PasswordResetLinkController@store` | `password.email` |
| GET | `reset-password/{token}` | `NewPasswordController@create` | `password.reset` |
| POST | `reset-password` | `NewPasswordController@store` | `password.store` |

### Autenticados (middleware `auth`)

| Método | URI | Controlador@método | Nombre |
|---|---|---|---|
| GET | `verify-email` | `EmailVerificationPromptController` | `verification.notice` |
| GET | `verify-email/{id}/{hash}` | `VerifyEmailController` (firmada, `throttle:6,1`) | `verification.verify` |
| POST | `email/verification-notification` | `EmailVerificationNotificationController@store` (`throttle:6,1`) | `verification.send` |
| GET | `confirm-password` | `ConfirmablePasswordController@show` | `password.confirm` |
| POST | `confirm-password` | `ConfirmablePasswordController@store` | — |
| PUT | `password` | `PasswordController@update` | `password.update` |
| POST | `logout` | `AuthenticatedSessionController@destroy` | `logout` |

`AuthenticatedSessionController@store` redirige a `admin.index` si el usuario autenticado `isAdmin()`, o al `intended`/`home` en caso contrario. `RegisteredUserController@store` asigna automáticamente el rol `Cliente` (lo crea con `firstOrCreate` si no existe) a todo usuario nuevo.
