# Roles y permisos

## Los roles

En vez de poner un booleano o un enum en la tabla de usuarios, hice una tabla `roles` con su modelo `Role`. Cada `User` tiene un `role_id` obligatorio, y la llave foránea tiene `restrictOnDelete`, o sea que no se puede borrar un rol si todavía hay usuarios con ese rol.

Los roles se crean con `RoleSeeder` (`database/seeders/RoleSeeder.php`). Usé `firstOrCreate` para que no se dupliquen si el seeder se corre varias veces.

| Rol | Descripción |
|---|---|
| `Administrador` | Acceso total al sistema |
| `Cliente` | Usuario que realiza reservas |

## Cómo sé si alguien es administrador

**No hay un campo `is_admin`.** Lo reviso por el **nombre del rol**, exacto y con mayúscula:

```php
// app/Models/User.php
public function isAdmin(): bool
{
    return $this->role?->nombre === 'Administrador';
}
```

Algo a tener en cuenta: como comparo con `===` contra `'Administrador'`, si alguien le cambia el nombre al rol (por ejemplo a `admin`) se dañan todos los permisos de admin sin que salga ningún error.

## Middleware admin

Está en `app/Http/Middleware/EnsureUserIsAdmin.php`:

```php
public function handle(Request $request, Closure $next): Response
{
    if (! $request->user() || ! $request->user()->isAdmin()) {
        abort(403, 'Acceso restringido al panel de administración.');
    }
    return $next($request);
}
```

Lo registré con el alias `admin` en `bootstrap/app.php` y lo uso en todo el grupo de rutas que empieza por `admin/` (ver [RUTAS.md](RUTAS.md)):

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // ...
});
```

Entonces, si alguien entra a `/admin/...` sin haber iniciado sesión, el middleware `auth` lo manda al login. Y si inició sesión pero no es admin, le sale **403** de una.

## Registro de usuarios nuevos

En `RegisteredUserController@store` (`app/Http/Controllers/Auth/RegisteredUserController.php`) a todo el que se registra desde el formulario le pongo el rol `Cliente`:

```php
$role = Role::firstOrCreate(['nombre' => 'Cliente'], ['descripcion' => 'Usuario que realiza reservas']);
```

Después de crear la cuenta lo mando al catálogo (`home`).

No hay forma de registrarse como admin desde la página. Los admins se crean a mano (con el seeder, con Tinker o cambiando el `role_id` en la base de datos), porque el panel de usuarios (`admin.usuarios.index`) por ahora es solo para ver.

## A dónde va cada uno después del login

En `AuthenticatedSessionController@store` (`app/Http/Controllers/Auth/AuthenticatedSessionController.php`):

- Si `auth()->user()->isAdmin()`, lo mando a `route('admin.index')`, que es el panel.
- Si no, `redirect()->intended(route('home'))`, o sea a la página que quería ver antes o al catálogo.

## Permisos sobre las reservas (que sea el dueño)

Aparte del middleware, dentro de los controladores reviso a mano que un cliente no pueda cancelar ni pagar reservas de otra persona:

```php
// ReservaController::cancelar() y PagoController::autorizarPropietario()
if ($reserva->detalle?->user_id !== auth()->id()) {
    abort(403);
}
```

Esto no tiene que ver con el rol, aplica para **cualquier** usuario logueado. Hasta un admin recibe 403 si intenta cancelar o pagar la reserva de otro por estas rutas. Si el admin necesita cambiarle el estado a una reserva ajena, lo hace desde `admin.reservas.cambiarEstado`, que no tiene esa restricción.

## Resumen de quién puede hacer qué

| Acción | Sin login | Cliente | Administrador |
|---|---|---|---|
| Ver el catálogo y el detalle de una habitación | ✅ | ✅ | ✅ |
| Reservar, cancelar o pagar su propia reserva | ❌ (tiene que iniciar sesión) | ✅ | ✅ (igual que cualquier usuario) |
| Ver "Mis reservas" | ❌ | ✅ (solo las suyas) | ✅ (solo las suyas) |
| Panel `/admin/*` (zonas, habitaciones, reservas, usuarios, notificaciones) | ❌ | ❌ | ✅ |
