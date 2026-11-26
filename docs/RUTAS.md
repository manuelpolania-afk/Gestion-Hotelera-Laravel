# Rutas

Las rutas del proyecto están en dos archivos: `routes/web.php`, que son las mías del hotel, y `routes/auth.php`, que viene de Breeze y lo incluyo al final de `web.php`.

## Públicas (no necesitan login)

| Método | URI | Controlador@método | Nombre |
|---|---|---|---|
| GET | `/` | `HomeController@index` | `home` |
| GET | `/habitaciones/{habitacion}` | `HabitacionController@show` | `habitaciones.show` |

En el home se puede mandar `?zona_id=` para filtrar el catálogo por zona.

## Con login (middleware `auth`)

| Método | URI | Controlador@método | Nombre |
|---|---|---|---|
| GET | `/dashboard` | Closure que devuelve `view('dashboard')` | `dashboard` (también pide `verified`) |
| GET | `/profile` | `ProfileController@edit` | `profile.edit` |
| PATCH | `/profile` | `ProfileController@update` | `profile.update` |
| DELETE | `/profile` | `ProfileController@destroy` | `profile.destroy` |
| POST | `/reservas` | `ReservaController@store` | `reservas.store` |
| GET | `/mis-reservas` | `ReservaController@misReservas` | `reservas.mias` |
| POST | `/reservas/{reserva}/cancelar` | `ReservaController@cancelar` | `reservas.cancelar` |
| GET | `/reservas/{reserva}/pago` | `PagoController@create` | `pagos.create` |
| POST | `/reservas/{reserva}/pago` | `PagoController@store` | `pagos.store` |

Cualquier usuario logueado (sea `Cliente` o `Administrador`) puede reservar, cancelar y pagar sus reservas. Que la reserva sea suya lo reviso dentro de cada controlador comparando `detalle.user_id` con `auth()->id()`.

## Panel de admin (middleware `auth` + `admin`, prefijo `admin`, nombres `admin.*`)

Usa `EnsureUserIsAdmin`, lo explico en [ROLES_Y_PERMISOS.md](ROLES_Y_PERMISOS.md).

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

Algunas cosas que hice a propósito:
- En el `Route::resource('habitaciones', ...)` quité `show`, porque esa vista es pública y está afuera del grupo de admin. También le puse `->parameters(['habitaciones' => 'habitacion'])` para que el parámetro quede en singular (`{habitacion}`).
- La ruta `admin/notificaciones/marcar-todas` la puse **antes** de `admin/notificaciones/{notificacion}/leida`, para que Laravel no confunda `marcar-todas` con el id de una notificación.
- Lo de usuarios (`admin.usuarios.index`) por ahora es **solo para ver**, no se pueden crear, editar ni borrar usuarios desde el panel.

## Autenticación (`routes/auth.php`)

### Sin sesión (middleware `guest`)

| Método | URI | Controlador@método | Nombre |
|---|---|---|---|
| GET | `register` | `RegisteredUserController@create` | `register` |
| POST | `register` | `RegisteredUserController@store` | (sin nombre) |
| GET | `login` | `AuthenticatedSessionController@create` | `login` |
| POST | `login` | `AuthenticatedSessionController@store` | (sin nombre) |
| GET | `forgot-password` | `PasswordResetLinkController@create` | `password.request` |
| POST | `forgot-password` | `PasswordResetLinkController@store` | `password.email` |
| GET | `reset-password/{token}` | `NewPasswordController@create` | `password.reset` |
| POST | `reset-password` | `NewPasswordController@store` | `password.store` |

### Con sesión (middleware `auth`)

| Método | URI | Controlador@método | Nombre |
|---|---|---|---|
| GET | `verify-email` | `EmailVerificationPromptController` | `verification.notice` |
| GET | `verify-email/{id}/{hash}` | `VerifyEmailController` (firmada, `throttle:6,1`) | `verification.verify` |
| POST | `email/verification-notification` | `EmailVerificationNotificationController@store` (`throttle:6,1`) | `verification.send` |
| GET | `confirm-password` | `ConfirmablePasswordController@show` | `password.confirm` |
| POST | `confirm-password` | `ConfirmablePasswordController@store` | (sin nombre) |
| PUT | `password` | `PasswordController@update` | `password.update` |
| POST | `logout` | `AuthenticatedSessionController@logout` | `logout` |

Después del login, `AuthenticatedSessionController@store` manda al admin a `admin.index` y a los demás a la página que querían ver (`intended`) o al `home`. Al registrarse, `RegisteredUserController@store` le pone el rol `Cliente` al usuario nuevo (si el rol no existe lo crea con `firstOrCreate`) y lo lleva al catálogo.
