# Arquitectura y modelo de datos

Aquí explico cómo organicé la base de datos y los modelos del proyecto. Básicamente todo gira alrededor de las reservas: un usuario reserva una habitación, la habitación está en una zona, y la reserva puede tener pagos y notificaciones.



En palabras:

- Cada `User` tiene un `Role`, que puede ser `Administrador` o `Cliente`.
- Una `Reserva` es de una `Habitacion` y tiene un `DetalleReserva` (1 a 1). En el detalle guardo quién reservó, las fechas y cuántas personas van.
- Una `Reserva` puede tener varios `Pago`, porque cada vez que el cliente le da a "Pagar" se guarda un registro, salga aprobado o rechazado.
- Una `Habitacion` pertenece a una `Zona` y puede tener varias imágenes (`ImagenHabitacion`).
- Cada `Notificacion` va para un administrador y apunta a una `Reserva`.

## Modelos (`app/Models`)

### User

| Campo | Tipo |
|---|---|
| `role_id` | FK a `roles.id` |
| `documento` | string, único |
| `name` | string |
| `email` | string, único |
| `telefono` | string, puede ser null |
| `estado` | enum `activo`/`inactivo`, por defecto `activo` |
| `password` | hash (cast `hashed`) |

- `role()` es un `BelongsTo` a `Role`.
- `notificaciones()` es un `HasMany` a `Notificacion`.
- `isAdmin()` devuelve `true` si el rol se llama `Administrador`. Decidí no poner un campo `is_admin`, entonces todo lo de permisos depende de este método.
- `scopeAdministradores()` me trae todos los usuarios con rol `Administrador`. Lo uso para mandarle la notificación a todos los admins cuando alguien reserva.

### Role

| Campo | Tipo |
|---|---|
| `nombre` | string, único (`Administrador`, `Cliente`) |
| `descripcion` | text, puede ser null |

- `users()` es un `HasMany` a `User`.

### Zona

| Campo | Tipo |
|---|---|
| `codigo_zona` | string, único |
| `descripcion` | text, puede ser null |

- `habitaciones()` es un `HasMany` a `Habitacion`.
- Una zona que tenga habitaciones no se deja borrar, eso lo valido en `ZonaController::destroy`.

### Habitacion

| Campo | Tipo |
|---|---|
| `zona_id` | FK a `zonas.id` |
| `nombre_habitacion` | string(50) |
| `descripcion` | text, puede ser null |
| `capacidad` | entero sin signo |
| `valor` | decimal(10,2), es lo que cuesta la noche |
| `estado` | enum `Disponible` / `Ocupada` / `Mantenimiento` / `Fuera de servicio`, por defecto `Disponible` |

- Relaciones: `zona()`, `imagenes()` y `imagenPrincipal()` (la imagen que tiene `principal = true`).
- `scopeDisponibles()` filtra por `estado = 'Disponible'`.
- Las habitaciones **no se borran de verdad**. Cuando el admin le da eliminar, `HabitacionController::destroy` la pasa a `Fuera de servicio`. Lo hice así para no perder el historial de reservas de esa habitación.

### ImagenHabitacion

La tabla se llama `imagenes_habitacion`. Esta no usa `created_at`/`updated_at` (le puse `$timestamps = false`), en vez de eso tiene `fecha_subida`.

| Campo | Tipo |
|---|---|
| `habitacion_id` | FK a `habitaciones.id`, `cascadeOnDelete` |
| `ruta` | string, la ruta dentro del disco `public` |
| `principal` | boolean, por defecto `false` |
| `fecha_subida` | timestamp, por defecto `CURRENT_TIMESTAMP` |

- `habitacion()` es un `BelongsTo`.
- Solo puede haber una imagen principal por habitación. Eso lo controlo en `ImagenHabitacionController::setPrincipal`: dentro de una transacción desmarco todas y marco la que se eligió.

### Reserva

| Campo | Tipo |
|---|---|
| `habitacion_id` | FK a `habitaciones.id` |
| `fecha` | date, el día en que se hizo la reserva |
| `estado_reserva` | enum `Pendiente` / `Confirmada` / `Cancelada` / `Finalizada`, por defecto `Pendiente` |
| `sub_total` | decimal(10,2), sale de `noches × valor de la habitación` |

- Relaciones: `habitacion()`, `detalle()` (HasOne a `DetalleReserva`), `pagos()` (HasMany a `Pago`) y `pagoAprobado()` (el último pago con `estado_pago = 'Aprobado'`).
- `scopeActivas()` deja por fuera las `Cancelada`.
- Tiene dos métodos estáticos que llamo cuando se listan reservas:
  - `finalizarVencidas()`: toda reserva `Confirmada` cuya `fecha_salida` ya pasó la pongo en `Finalizada`.
  - `sincronizarEstadosHabitaciones()`: pone `Ocupada` las habitaciones que tienen una reserva `Confirmada` para **hoy**, y vuelve a `Disponible` las que ya no la tienen (sin tocar las que están en `Mantenimiento` o `Fuera de servicio`).

El ciclo completo de una reserva lo explico en [MODULO_RESERVAS.md](MODULO_RESERVAS.md).

### DetalleReserva

| Campo | Tipo |
|---|---|
| `reserva_id` | FK a `reservas.id`, `cascadeOnDelete` |
| `user_id` | FK a `users.id`, `restrictOnDelete`, es el cliente que reservó |
| `fecha_ingreso` | date |
| `fecha_salida` | date |
| `cantidad_personas` | entero sin signo |

- Relaciones: `reserva()` y `user()`.
- La separé de `reservas` tipo cabecera/detalle (como una orden de compra), así la reserva queda con lo general y el detalle con los datos del huésped.

> Nota: al principio la migración de esta tabla tenía un error de tipeo, decía `'u ser_id'` con un espacio. Si se corría en una base nueva creaba la columna con ese espacio y se dañaba todo el módulo de reservas. Ya lo arreglé en `database/migrations/2026_09_06_164912_create_detalle_reservas_table.php`.

### Pago

| Campo | Tipo |
|---|---|
| `reserva_id` | FK a `reservas.id`, `cascadeOnDelete` |
| `metodo_pago` | string (`Tarjeta`, `PSE`, `Efectivo`) |
| `estado_pago` | string (`Pendiente`, `Aprobado`, `Rechazado`), por defecto `Pendiente` |
| `monto` | decimal(10,2) |
| `referencia` | string, único, con formato `PAY-XXXXXXXX` |
| `tarjeta_enmascarada` | string, puede ser null, solo los últimos 4 dígitos (`**** **** **** 1234`) |
| `fecha_pago` | timestamp, puede ser null, solo se llena si el pago se aprobó |

- `reserva()` es un `BelongsTo`.
- Una reserva puede tener **varios** pagos, porque guardo todos los intentos (también los rechazados).
- Cómo funciona todo el pago está en [MODULO_PAGOS.md](MODULO_PAGOS.md).

### Notificacion

La tabla se llama `notificaciones`.

| Campo | Tipo |
|---|---|
| `user_id` | FK a `users.id`, `cascadeOnDelete`, es el admin que la recibe |
| `reserva_id` | FK a `reservas.id`, `cascadeOnDelete` |
| `mensaje` | string |
| `leida` | boolean, por defecto `false` |
| `fecha` | timestamp, por defecto `CURRENT_TIMESTAMP` |

- Relaciones: `user()` y `reserva()`.
- `scopeNoLeidas()` filtra `leida = false`.
- Se crean solas para **todos** los admins cada vez que un cliente hace una reserva (en `ReservaController::store`).

## Tablas y migraciones

| Tabla | Migración | Notas |
|---|---|---|
| `roles` | `0000_01_01_000000_create_roles_table.php` | `nombre` único |
| `users` | `0001_01_01_000000_create_users_table.php` | en el mismo archivo vienen `password_reset_tokens` y `sessions` |
| `zonas` | `2026_09_03_140000_create_zonas_table.php` | |
| `habitaciones` | `2026_09_03_140001_create_habitaciones_table.php` + `2026_09_18_120000_add_descripcion_to_habitaciones_table.php` | la `descripcion` la agregué después en otra migración |
| `imagenes_habitacion` | `2026_09_03_140002_create_imagen_habitacions_table.php` | ojo, el archivo dice `imagen_habitacions` pero la tabla se llama `imagenes_habitacion` |
| `reservas` | `2026_09_06_164854_create_reservas_table.php` | |
| `detalle_reservas` | `2026_09_06_164912_create_detalle_reservas_table.php` | |
| `notificaciones` | `2026_09_09_124427_create_notificacions_table.php` | igual, el archivo dice `notificacions` y la tabla es `notificaciones` |
| `pagos` | `2026_09_16_171120_create_pagos_table.php` | pago simulado |

También están las tablas que trae Laravel por defecto (`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`), pero esas no son del negocio del hotel.

## Tecnologías que usé

- **PHP** `^8.3` y **Laravel** `^13.17`.
- Para desarrollo: PHPUnit `^12.5`, Laravel Pint (formato), Laravel Pail (ver logs), Faker y Mockery.
- **Frontend**: Tailwind CSS, Alpine.js `^3.4` y Vite `^8` con `laravel-vite-plugin`.
  - Algo que tengo pendiente: en `package.json` quedaron mezclados `@tailwindcss/vite ^4.0.0` (de Tailwind 4) y `tailwindcss ^3.1.0` (Tailwind 3). Funciona porque estoy usando la configuración clásica con PostCSS (`tailwind.config.js` + `@layer components`), pero si algún día paso a Tailwind 4 completo toca alinear eso.
- **Login y registro**: partí del scaffolding de Laravel Breeze (los controladores están en `app/Http/Controllers/Auth`).

Lo de permisos está en [ROLES_Y_PERMISOS.md](ROLES_Y_PERMISOS.md).
