# Pruebas automatizadas

## Cómo correr la suite

```bash
composer test
# equivalente a:
php artisan config:clear
php artisan test
```

## Estado actual

El proyecto usa PHPUnit `^12.5` sobre el runner de Laravel. Toda la suite existente corresponde al **scaffolding de autenticación de Laravel Breeze** — no hay tests del dominio propio del hotel.

| Archivo | Cubre |
|---|---|
| `tests/Feature/Auth/AuthenticationTest.php` | Render de `/login`, login exitoso (redirige a `dashboard`), rechazo con password inválido, logout |
| `tests/Feature/Auth/RegistrationTest.php` | Render y envío exitoso del formulario de registro |
| `tests/Feature/Auth/EmailVerificationTest.php` | Pantalla y proceso de verificación de email |
| `tests/Feature/Auth/PasswordConfirmationTest.php` | Pantalla/flujo de confirmación de contraseña |
| `tests/Feature/Auth/PasswordResetTest.php` | Solicitud y reseteo de contraseña vía link |
| `tests/Feature/Auth/PasswordUpdateTest.php` | Actualización de contraseña autenticado |
| `tests/Feature/ProfileTest.php` | Ver/editar perfil, invalidación de `email_verified_at` al cambiar el email, eliminación de cuenta propia (con password correcto/incorrecto) |
| `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php` | Tests de ejemplo del scaffolding de Laravel (no aportan cobertura real) |

## Cobertura ausente (dominio del hotel)

No hay ningún test automatizado para:

- **Habitaciones y zonas**: CRUD, restricción de borrado de zonas con habitaciones, cambio de habitación a `Fuera de servicio` al "eliminarla", subida/gestión de imágenes.
- **Reservas**: validación de capacidad, disponibilidad, solapamiento de fechas, cálculo de `sub_total`, cancelación, `finalizarVencidas()`, `sincronizarEstadosHabitaciones()`.
- **Pagos simulados**: aprobación/rechazo (incluido el caso determinístico de tarjeta terminada en `0000`), creación de la referencia, cambio de estado de la reserva a `Confirmada`, autorización de propietario.
- **Roles y middleware `admin`**: acceso denegado a no-administradores, redirección post-login según rol.
- **Notificaciones**: creación al reservar, marcar como leídas (individual y masivo), autorización por usuario.

## Sugerencia de próximos tests (si se quiere ampliar cobertura)

Si el instructor pide evidencia de pruebas sobre el dominio, los candidatos de mayor valor por esfuerzo son:

1. `PagoControllerTest`: pago aprobado confirma la reserva; tarjeta `0000` siempre rechaza; usuario no dueño recibe `403`; reserva no `Pendiente` no permite pagar de nuevo.
2. `ReservaControllerTest`: no se puede reservar una habitación no disponible; no se puede reservar con fechas solapadas; el cálculo de `sub_total` es correcto.
3. `EnsureUserIsAdminTest`: un cliente recibe `403` al entrar a cualquier ruta `/admin/*`.

Estos se pueden generar con `php artisan make:test PagoControllerTest` (usan `RefreshDatabase` + factories de `User`, `Habitacion`, `Reserva`).
