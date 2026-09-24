# Arquitectura y modelo de datos

## Diagrama de relaciones (resumen)

```
Role 1───N User 1───N Notificacion N───1 Reserva
                       │                    │
                       │                    ├──1 DetalleReserva N───1 User (cliente)
                       │                    ├──N Pago
                       │                    └──1 Habitacion N───1 Zona
                                                      └──N ImagenHabitacion
```

- Un `User` tiene un `Role` (`Administrador` o `Cliente`).
- Una `Reserva` pertenece a una `Habitacion` y tiene un `DetalleReserva` (1:1) que a su vez identifica al `User` que reservó y las fechas/personas.
- Una `Reserva` puede tener varios intentos de `Pago` (1:N) — cada clic en "Pagar" genera un registro, aprobado o rechazado.
- Una `Habitacion` pertenece a una `Zona` y tiene varias `ImagenHabitacion`.
- Cada `Notificacion` está dirigida a un `User` (administrador) y referencia una `Reserva`.

## Modelos (`app/Models`)

### `User`

| Campo | Tipo |
|---|---|
| `role_id` | FK → `roles.id` |
| `documento` | string, único |
| `name` | string |
| `email` | string, único |
| `telefono` | string, nullable |
| `estado` | enum `activo`/`inactivo`, default `activo` |
| `password` | hash (cast `hashed`) |

- Relación `role(): BelongsTo` → `Role`.
- Relación `notificaciones(): HasMany` → `Notificacion`.
- Método `isAdmin(): bool` → `true` si `role.nombre === 'Administrador'`. Es la única fuente de verdad para permisos de administrador (no existe un booleano `is_admin`).
- Scope `scopeAdministradores()` → todos los usuarios cuyo rol es `Administrador`. Se usa para notificar a todo el staff cuando se crea una reserva.

### `Role`

| Campo | Tipo |
|---|---|
| `nombre` | string, único (`Administrador`, `Cliente`) |
| `descripcion` | text, nullable |

- Relación `users(): HasMany` → `User`.

### `Zona`

| Campo | Tipo |
|---|---|
| `codigo_zona` | string, único |
| `descripcion` | text, nullable |

- Relación `habitaciones(): HasMany` → `Habitacion`.
- No se puede eliminar una zona con habitaciones asociadas (validado en `ZonaController::destroy`).

### `Habitacion`

| Campo | Tipo |
|---|---|
| `zona_id` | FK → `zonas.id` |
| `nombre_habitacion` | string(50) |
| `descripcion` | text, nullable |
| `capacidad` | entero sin signo |
| `valor` | decimal(10,2) — tarifa por noche |
| `estado` | enum `Disponible` / `Ocupada` / `Mantenimiento` / `Fuera de servicio`, default `Disponible` |

- Relaciones: `zona(): BelongsTo`, `imagenes(): HasMany` → `ImagenHabitacion`, `imagenPrincipal(): HasOne` (la imagen con `principal = true`).
- Scope `scopeDisponibles()` → `estado = 'Disponible'`.
- **No se elimina físicamente**: `HabitacionController::destroy` la pasa a `Fuera de servicio` para conservar el historial de reservas asociadas.

### `ImagenHabitacion`

Tabla física: `imagenes_habitacion`. **No usa timestamps `created_at`/`updated_at`** (`$timestamps = false`); en su lugar tiene `fecha_subida`.

| Campo | Tipo |
|---|---|
| `habitacion_id` | FK → `habitaciones.id`, `cascadeOnDelete` |
| `ruta` | string — ruta relativa en el disco `public` |
| `principal` | boolean, default `false` |
| `fecha_subida` | timestamp, default `CURRENT_TIMESTAMP` |

- Relación `habitacion(): BelongsTo`.
- Solo una imagen por habitación puede tener `principal = true` (se controla en `ImagenHabitacionController::setPrincipal`, que desmarca las demás dentro de una transacción).

### `Reserva`

| Campo | Tipo |
|---|---|
| `habitacion_id` | FK → `habitaciones.id` |
| `fecha` | date — fecha de creación de la reserva |
| `estado_reserva` | enum `Pendiente` / `Confirmada` / `Cancelada` / `Finalizada`, default `Pendiente` |
| `sub_total` | decimal(10,2) — calculado como `días × personas × valor de la habitación` |

- Relaciones: `habitacion(): BelongsTo`, `detalle(): HasOne` → `DetalleReserva`, `pagos(): HasMany` → `Pago`, `pagoAprobado(): HasOne` (el último `Pago` con `estado_pago = 'Aprobado'`).
- Scope `scopeActivas()` → excluye `Cancelada`.
- Métodos de negocio estáticos (se invocan al listar reservas):
  - `finalizarVencidas()`: pasa a `Finalizada` toda reserva `Confirmada` cuya `fecha_salida` ya pasó.
  - `sincronizarEstadosHabitaciones()`: marca `Ocupada` las habitaciones con una reserva `Confirmada` vigente **hoy**, y `Disponible` las que dejaron de tenerla (respetando `Mantenimiento`/`Fuera de servicio`).

Ver el ciclo de vida completo en [MODULO_RESERVAS.md](MODULO_RESERVAS.md).

### `DetalleReserva`

| Campo | Tipo |
|---|---|
| `reserva_id` | FK → `reservas.id`, `cascadeOnDelete` |
| `user_id` | FK → `users.id`, `restrictOnDelete` — el cliente que hizo la reserva |
| `fecha_ingreso` | date |
| `fecha_salida` | date |
| `cantidad_personas` | entero sin signo |

- Relaciones: `reserva(): BelongsTo`, `user(): BelongsTo`.
- Existe como tabla separada de `reservas` para poder identificar al cliente y las fechas sin sobrecargar el modelo `Reserva` (separación cabecera/detalle, similar a un patrón de orden de compra).

> **Nota histórica**: la migración original de esta tabla tenía un typo (`'u ser_id'` con espacio en lugar de `'user_id'`) que, de ejecutarse en una base de datos nueva, habría creado una columna con espacio en el nombre y roto todo el módulo de reservas. Ya fue corregido en `database/migrations/2026_09_06_164912_create_detalle_reservas_table.php`.

### `Pago`

| Campo | Tipo |
|---|---|
| `reserva_id` | FK → `reservas.id`, `cascadeOnDelete` |
| `metodo_pago` | string (`Tarjeta`, `PSE`, `Efectivo`) |
| `estado_pago` | string (`Pendiente` inicial, `Aprobado`, `Rechazado`), default `Pendiente` |
| `monto` | decimal(10,2) |
| `referencia` | string, único — formato `PAY-XXXXXXXX` |
| `tarjeta_enmascarada` | string, nullable — últimos 4 dígitos (`**** **** **** 1234`) |
| `fecha_pago` | timestamp, nullable — se llena solo si el pago fue aprobado |

- Relación `reserva(): BelongsTo`.
- Una reserva puede tener **varios** registros de `Pago` (cada intento, incluidos los rechazados, queda guardado para trazabilidad).
- Detalle completo del flujo en [MODULO_PAGOS.md](MODULO_PAGOS.md).

### `Notificacion`

Tabla física: `notificaciones`.

| Campo | Tipo |
|---|---|
| `user_id` | FK → `users.id`, `cascadeOnDelete` — el administrador destinatario |
| `reserva_id` | FK → `reservas.id`, `cascadeOnDelete` |
| `mensaje` | string |
| `leida` | boolean, default `false` |
| `fecha` | timestamp, default `CURRENT_TIMESTAMP` |

- Relaciones: `user(): BelongsTo`, `reserva(): BelongsTo`.
- Scope `scopeNoLeidas()` → `leida = false`.
- Se generan automáticamente para **todos** los administradores cada vez que un cliente crea una reserva (`ReservaController::store`).

## Esquema completo de base de datos

| Tabla | Migración | Notas |
|---|---|---|
| `roles` | `0000_01_01_000000_create_roles_table.php` | `nombre` único |
| `users` | `0001_01_01_000000_create_users_table.php` | incluye `password_reset_tokens` y `sessions` en el mismo archivo |
| `zonas` | `2026_09_03_140000_create_zonas_table.php` | |
| `habitaciones` | `2026_09_03_140001_create_habitaciones_table.php` + `2026_09_18_120000_add_descripcion_to_habitaciones_table.php` | la columna `descripcion` se agregó en una migración posterior |
| `imagenes_habitacion` | `2026_09_03_140002_create_imagen_habitacions_table.php` | ⚠️ el nombre de archivo dice `imagen_habitacions`, pero la tabla real se llama `imagenes_habitacion` |
| `reservas` | `2026_09_06_164854_create_reservas_table.php` | |
| `detalle_reservas` | `2026_09_06_164912_create_detalle_reservas_table.php` | |
| `notificaciones` | `2026_09_09_124427_create_notificacions_table.php` | ⚠️ el nombre de archivo dice `notificacions`, la tabla real es `notificaciones` |
| `pagos` | `2026_09_16_171120_create_pagos_table.php` | pago simulado |

> Las tablas de infraestructura estándar de Laravel (`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`) también existen pero no son parte del dominio de negocio.

## Stack técnico y dependencias

- **PHP** `^8.3`, **Laravel Framework** `^13.17`.
- Dev: PHPUnit `^12.5`, Laravel Pint (formateo), Laravel Pail (logs en vivo), Faker, Mockery.
- **Frontend**: Tailwind CSS, Alpine.js `^3.4`, Vite `^8` + `laravel-vite-plugin`.
  - ⚠️ `package.json` combina `@tailwindcss/vite ^4.0.0` (plugin de Tailwind v4) con el paquete `tailwindcss ^3.1.0` (v3). Funciona porque el proyecto usa la configuración clásica de PostCSS (`tailwind.config.js` + `@layer components`), pero conviene alinear versiones si se planea migrar a la configuración nativa de Tailwind v4.
- **Autenticación**: scaffolding de Laravel Breeze (controladores en `app/Http/Controllers/Auth`).

Ver detalle del sistema de permisos en [ROLES_Y_PERMISOS.md](ROLES_Y_PERMISOS.md).
