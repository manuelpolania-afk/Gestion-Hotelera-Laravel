# Roles y permisos

## Modelo de roles

El sistema usa una tabla `roles` (modelo `Role`) en vez de un simple enum o booleano. Cada `User` tiene un `role_id` obligatorio (FK `restrictOnDelete`: no se puede borrar un rol que todavía tenga usuarios).

Roles creados por `RoleSeeder` (`database/seeders/RoleSeeder.php`, con `firstOrCreate` para no duplicar si ya existen):

| Rol | Descripción |
|---|---|
| `Administrador` | Acceso total al sistema |
| `Cliente` | Usuario que realiza reservas |

## Cómo se determina si un usuario es administrador

**No existe un campo `is_admin`.** La verificación se hace por el **nombre exacto del rol** (sensible a mayúsculas):

```php
// app/Models/User.php
public function isAdmin(): bool
{
    return $this->role?->nombre === 'Administrador';
}
```

Esto implica que renombrar el rol `Administrador` (por ejemplo a `admin` en minúscula) rompería silenciosamente todos los permisos de administrador, ya que la comparación es un `===` exacto contra el string `'Administrador'`.

## Middleware `admin`

Archivo: `app/Http/Middleware/EnsureUserIsAdmin.php`.

```php
public function handle(Request $request, Closure $next): Response
{
    if (! $request->user() || ! $request->user()->isAdmin()) {
        abort(403, 'Acceso restringido al panel de administración.');
    }
    return $next($request);
}
```

Registrado con el alias `admin` en `bootstrap/app.php` y aplicado a todo el grupo de rutas bajo el prefijo `admin/` (ver [RUTAS.md](RUTAS.md)):

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // ...
});
```

Cualquier intento de acceder a `/admin/*` sin sesión iniciada es redirigido al login (por el middleware `auth`); si hay sesión pero el usuario no es administrador, recibe un **403** directo.

## Registro de nuevos usuarios

`RegisteredUserController@store` (`app/Http/Controllers/Auth/RegisteredUserController.php`) siempre asigna el rol `Cliente` a las cuentas creadas desde el formulario público de registro:

```php
$role = Role::firstOrCreate(['nombre' => 'Cliente'], ['descripcion' => 'Usuario que realiza reservas']);
```

No existe ningún flujo público para registrarse como administrador — los administradores se crean manualmente (seeder, Tinker, o por otro administrador) modificando `role_id` en la base de datos, ya que el panel de usuarios (`admin.usuarios.index`) es de solo lectura.

## Redirección post-login según rol

`AuthenticatedSessionController@store` (`app/Http/Controllers/Auth/AuthenticatedSessionController.php`) redirige de forma distinta tras un login exitoso:

- Si `auth()->user()->isAdmin()` → `route('admin.index')` (panel de administración).
- Si no → `redirect()->intended(route('home'))` (catálogo público / última página pretendida).

## Autorización a nivel de recurso (propietario de una reserva)

Fuera del middleware `admin`, hay un segundo nivel de autorización manual dentro de los controladores, para que un cliente no pueda cancelar ni pagar reservas ajenas:

```php
// ReservaController::cancelar() y PagoController::autorizarPropietario()
if ($reserva->detalle?->user_id !== auth()->id()) {
    abort(403);
}
```

Esto no depende de roles: aplica a **cualquier** usuario autenticado (incluidos administradores, que tampoco pueden cancelar/pagar reservas ajenas desde estas rutas — para cambiar el estado de una reserva de otro usuario, un admin usa `admin.reservas.cambiarEstado`, que no tiene esta restricción de propietario).

## Resumen de matriz de acceso

| Acción | Invitado | Cliente autenticado | Administrador |
|---|---|---|---|
| Ver catálogo / detalle de habitación | ✅ | ✅ | ✅ |
| Reservar / cancelar / pagar su propia reserva | ❌ (requiere login) | ✅ | ✅ (como cualquier usuario autenticado) |
| Ver "Mis reservas" | ❌ | ✅ (solo las propias) | ✅ (solo las propias) |
| Panel `/admin/*` (zonas, habitaciones, reservas, usuarios, notificaciones) | ❌ | ❌ | ✅ |
