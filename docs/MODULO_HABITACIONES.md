# Módulo de habitaciones y zonas

Gestiona el catálogo del hotel: zonas (alas/sectores del edificio), habitaciones dentro de cada zona y sus imágenes.

## Zonas

CRUD completo desde el panel admin (`ZonaController`, rutas `admin.zonas.*`).

- **Crear/editar** (`store`/`update`): valida `codigo_zona` (`required`, `unique` — ignorando el propio registro al editar) y `descripcion` (`nullable`).
- **Listar** (`index`): incluye `withCount('habitaciones')` para mostrar cuántas habitaciones tiene cada zona.
- **Eliminar** (`destroy`): **rechaza** el borrado si la zona tiene habitaciones asociadas (`$zona->habitaciones()->exists()`), devolviendo un mensaje de error en vez de borrar — evita dejar habitaciones huérfanas.

Zonas de ejemplo cargadas por `ZonaSeeder`:

| `codigo_zona` | Descripción |
|---|---|
| Norte | Habitaciones en el ala norte del hotel |
| Sur | Habitaciones en el ala sur del hotel |
| VIP | Suites y habitaciones premium |

## Habitaciones

CRUD desde el panel admin (`HabitacionController`, rutas `admin.habitaciones.*`) más una vista pública de detalle.

### Campos y validación

`StoreHabitacionRequest` / `UpdateHabitacionRequest` (`app/Http/Requests`):

| Campo | Regla |
|---|---|
| `zona_id` | `required`, `integer`, `exists:zonas,id` |
| `nombre_habitacion` | `required`, `string`, `max:50` |
| `descripcion` | `nullable`, `string`, `max:1000` |
| `capacidad` | `required`, `integer`, `min:1` |
| `valor` | `required`, `numeric`, `min:0` — tarifa **por noche** |
| `estado` | (solo al editar) `required`, `in:Disponible,Ocupada,Mantenimiento,Fuera de servicio` |

Al **crear**, el `estado` siempre se fuerza a `Disponible` en el controlador — no es un campo del formulario de creación, solo del de edición.

### Estados de una habitación

| Estado | Significado |
|---|---|
| `Disponible` | Se puede reservar |
| `Ocupada` | Tiene una reserva `Confirmada` vigente hoy (se sincroniza automáticamente, ver [MODULO_RESERVAS.md](MODULO_RESERVAS.md)) |
| `Mantenimiento` | Fuera de servicio temporalmente, gestión manual del admin |
| `Fuera de servicio` | Deshabilitada permanentemente (equivalente al "borrado" de una habitación) |

**Eliminar una habitación no la borra de la base de datos**: `HabitacionController::destroy` cambia su `estado` a `Fuera de servicio`, para conservar el historial de reservas que la referencian (`reservas.habitacion_id` no permite `cascadeOnDelete`, usa `restrictOnDelete`).

### Vista pública de detalle

`HabitacionController@show` (ruta `habitaciones.show`, pública) carga la zona y todas las imágenes ordenadas con la principal primero (`principal` desc, `fecha_subida` asc), y renderiza `resources/views/habitaciones/show.blade.php` con el layout `x-site-layout`.

Si la habitación tiene `descripcion`, se muestra completa en esa vista de detalle y como extracto de 2 líneas (`line-clamp-2`) en la tarjeta del catálogo (`resources/views/home/index.blade.php`). El campo es opcional: si está vacío, simplemente no se renderiza esa sección.

### Catálogo / home

`HomeController@index` (ruta `home`) lista zonas y solo habitaciones con `estado = 'Disponible'` (scope `scopeDisponibles`), con filtro opcional por `?zona_id=`.

## Imágenes de habitación

`ImagenHabitacionController` — todas las rutas requieren `auth` + `admin`.

- **Subir** (`store`, `admin.habitaciones.imagenes.store`): acepta un array `imagenes[]`, cada archivo validado como `image`, `mimes:jpg,jpeg,png,webp`, `max:4096` KB. Se guardan en el disco `public`, carpeta `habitaciones`. Si la habitación no tenía ninguna imagen previa, la **primera** del lote subido se marca automáticamente como `principal`.
- **Marcar como principal** (`setPrincipal`, `admin.imagenes.principal`): dentro de una transacción, desmarca todas las imágenes de esa habitación y marca la seleccionada — garantiza que solo haya una `principal = true` por habitación.
- **Eliminar** (`destroy`, `admin.imagenes.destroy`): borra el archivo físico del disco `public` y el registro en base de datos.

Requiere que exista el enlace simbólico `public/storage` (`php artisan storage:link`) para que las imágenes sean accesibles vía HTTP — ver [INSTALACION.md](INSTALACION.md).
