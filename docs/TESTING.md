# Pruebas

## Cómo correrlas

```bash
composer test
# que es lo mismo que:
php artisan config:clear
php artisan test
```

## Lo que hay ahora

Uso PHPUnit `^12.5` con el runner de Laravel. Siendo sincero, todas las pruebas que hay ahorita son las que vienen con **Breeze** para el login y el registro. Todavía no tengo pruebas de la parte del hotel.

| Archivo | Qué prueba |
|---|---|
| `tests/Feature/Auth/AuthenticationTest.php` | que cargue `/login`, que el login funcione (redirige a `dashboard`), que rechace una contraseña mala y el logout |
| `tests/Feature/Auth/RegistrationTest.php` | que cargue el registro y que se pueda registrar |
| `tests/Feature/Auth/EmailVerificationTest.php` | la pantalla y el proceso de verificar el correo |
| `tests/Feature/Auth/PasswordConfirmationTest.php` | la confirmación de contraseña |
| `tests/Feature/Auth/PasswordResetTest.php` | pedir el link y cambiar la contraseña |
| `tests/Feature/Auth/PasswordUpdateTest.php` | cambiar la contraseña estando logueado |
| `tests/Feature/ProfileTest.php` | ver y editar el perfil, que se borre `email_verified_at` al cambiar el correo, y eliminar la cuenta (con contraseña buena y mala) |
| `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php` | los de ejemplo de Laravel, no prueban nada real |

## Lo que me falta probar

No tengo pruebas automáticas de:

- **Habitaciones y zonas**: el CRUD, que no deje borrar zonas con habitaciones, que al "eliminar" una habitación quede `Fuera de servicio`, y lo de las fotos.
- **Reservas**: capacidad, disponibilidad, fechas cruzadas, el cálculo del `sub_total`, cancelar, `finalizarVencidas()` y `sincronizarEstadosHabitaciones()`.
- **Pagos**: aprobado y rechazado (incluyendo la tarjeta que termina en `0000`), la referencia, que la reserva pase a `Confirmada` y que solo el dueño pueda pagar.
- **Roles y middleware admin**: que un cliente no pueda entrar al panel y que el login mande a cada uno a su lugar.
- **Notificaciones**: que se creen al reservar, marcarlas como leídas (una o todas) y que cada admin solo toque las suyas.

## Las que haría primero

Si el profe pide pruebas del hotel, estas son las que más valen la pena por el tiempo que toman:

1. `PagoControllerTest`: que un pago aprobado confirme la reserva, que la tarjeta `0000` siempre se rechace, que otro usuario reciba `403` y que no se pueda pagar una reserva que ya no está `Pendiente`.
2. `ReservaControllerTest`: que no deje reservar una habitación que no está disponible, que no deje reservar fechas que se cruzan y que el `sub_total` dé bien.
3. `EnsureUserIsAdminTest`: que un cliente reciba `403` en cualquier ruta de `/admin/*`.

Se crean con `php artisan make:test PagoControllerTest` y usarían `RefreshDatabase` con factories de `User`, `Habitacion` y `Reserva`.
