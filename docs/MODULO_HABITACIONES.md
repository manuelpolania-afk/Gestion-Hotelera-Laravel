# Módulo de habitaciones y zonas

Este módulo es el catálogo del hotel. Tengo zonas (las alas o sectores del hotel), las habitaciones que hay en cada zona y las fotos de cada habitación.

## Zonas

Es un CRUD normal desde el panel de admin (`ZonaController`, rutas `admin.zonas.*`).

- **Crear y editar** (`store` / `update`): valido que `codigo_zona` sea obligatorio y único (al editar ignoro el mismo registro para que no choque consigo mismo). La `descripcion` es opcional.
- **Listar** (`index`): uso `withCount('habitaciones')` para mostrar cuántas habitaciones tiene cada zona.
- **Eliminar** (`destroy`): si la zona tiene habitaciones (`$zona->habitaciones()->exists()`) no la borro y muestro un mensaje de error. Así no quedan habitaciones sin zona.

Las zonas que cargo con `ZonaSeeder`:

| `codigo_zona` | Descripción |
|---|---|
| Norte | Habitaciones en el ala norte del hotel |
| Sur | Habitaciones en el ala sur del hotel |
| VIP | Suites y habitaciones premium |

## Habitaciones

También es un CRUD en el panel de admin (`HabitacionController`, rutas `admin.habitaciones.*`), y aparte hay una vista pública para ver el detalle de cada habitación.

### Validaciones

Las hice en `StoreHabitacionRequest` y `UpdateHabitacionRequest` (`app/Http/Requests`):

| Campo | Regla |
|---|---|
| `zona_id` | `required`, `integer`, `exists:zonas,id` |
| `nombre_habitacion` | `required`, `string`, `max:50` |
| `descripcion` | `nullable`, `string`, `max:1000` |
| `capacidad` | `required`, `integer`, `min:1` |
| `valor` | `required`, `numeric`, `min:0` (es el precio **por noche**) |
| `estado` | solo al editar: `required`, `in:Disponible,Ocupada,Mantenimiento,Fuera de servicio` |

Cuando se **crea** una habitación el estado siempre queda en `Disponible`, eso lo pongo en el controlador. El campo de estado solo aparece en el formulario de editar.

### Estados

| Estado | Qué significa |
|---|---|
| `Disponible` | se puede reservar |
| `Ocupada` | tiene una reserva `Confirmada` para hoy (esto se actualiza solo, lo explico en [MODULO_RESERVAS.md](MODULO_RESERVAS.md)) |
| `Mantenimiento` | no se puede usar por un tiempo, lo cambia el admin a mano |
| `Fuera de servicio` | quedó deshabilitada (es lo que pasa cuando se "elimina") |

**Cuando se elimina una habitación no se borra de la base de datos.** `HabitacionController::destroy` le cambia el estado a `Fuera de servicio`. Lo hice así porque las reservas viejas apuntan a esa habitación y además `reservas.habitacion_id` tiene `restrictOnDelete`, entonces no se podría borrar de todas formas.

### Vista de detalle (pública)

`HabitacionController@show` (ruta `habitaciones.show`) no necesita login. Carga la zona y todas las fotos, con la principal de primeras (ordeno por `principal` desc y `fecha_subida` asc), y muestra `resources/views/habitaciones/show.blade.php` con el layout `x-site-layout`.

Si la habitación tiene `descripcion`, en el detalle sale completa y en la tarjeta del catálogo (`resources/views/home/index.blade.php`) sale cortada a 2 líneas con `line-clamp-2`. Si no tiene descripción simplemente no se muestra esa parte.

### Catálogo (home)

`HomeController@index` (ruta `home`) muestra las zonas y solo las habitaciones que están `Disponible` (con `scopeDisponibles`). Se puede filtrar por zona con `?zona_id=`.

## Fotos de las habitaciones

Todo está en `ImagenHabitacionController` y todas las rutas piden `auth` + `admin`.

- **Subir** (`store`, `admin.habitaciones.imagenes.store`): recibe un arreglo `imagenes[]` y cada archivo tiene que ser `image`, `mimes:jpg,jpeg,png,webp` y máximo `4096` KB. Se guardan en el disco `public`, carpeta `habitaciones`. Si la habitación no tenía fotos, la **primera** que se sube queda como principal.
- **Marcar como principal** (`setPrincipal`, `admin.imagenes.principal`): en una transacción desmarco todas las fotos de esa habitación y marco la elegida, así nunca hay dos principales.
- **Eliminar** (`destroy`, `admin.imagenes.destroy`): borra el archivo del disco y el registro de la base de datos.

Para que las fotos se vean tiene que existir el enlace `public/storage` (`php artisan storage:link`), eso está en [INSTALACION.md](INSTALACION.md).
