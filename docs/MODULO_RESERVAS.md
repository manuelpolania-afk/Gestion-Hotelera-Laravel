# Módulo de reservas

Este es el módulo principal del proyecto. Acá explico cómo se crea una reserva, por qué estados pasa y cómo se mantienen actualizadas las habitaciones.

## Cómo guardo una reserva

La reserva la partí en dos tablas:

- **`reservas`**: lo general. La habitación, la fecha en que se hizo, el estado y el subtotal.
- **`detalle_reservas`** (1 a 1 con `reservas`): quién reservó (`user_id`), las fechas de entrada y salida y cuántas personas van.

Lo separé así para poder manejar el estado y el subtotal en la reserva sin mezclarlo con los datos del huésped.

## Estados de una reserva (`estado_reserva`)

```
Pendiente ──(pago aprobado)──> Confirmada ──(fecha_salida pasó)──> Finalizada
    │                               │
    └──────────(cancelar)──────────┴──> Cancelada
```

| Estado | Cuándo pasa | Quién lo cambia |
|---|---|---|
| `Pendiente` | apenas se crea la reserva | nadie, es el inicial |
| `Confirmada` | cuando el cliente paga y el pago simulado sale aprobado (ver [MODULO_PAGOS.md](MODULO_PAGOS.md)), o si un admin la cambia a mano | el sistema o el admin |
| `Cancelada` | cuando el cliente cancela una reserva `Pendiente` o `Confirmada`, o si un admin la cambia | el cliente dueño o el admin |
| `Finalizada` | sola: cada vez que se abre "Mis reservas" o el listado del admin corre `Reserva::finalizarVencidas()`, que pasa a `Finalizada` las `Confirmada` cuya `fecha_salida` ya pasó | el sistema |

El admin además puede poner cualquier estado desde `admin.reservas.cambiarEstado` (el select de la tabla del panel). Ahí no aplico las validaciones que sí tiene el flujo del cliente.

## Crear una reserva: `ReservaController@store`

Ruta `POST /reservas` (`reservas.store`). Hay que estar logueado, cualquier usuario puede reservar.

Primero valida el formulario con `StoreReservaRequest`:

| Campo | Regla |
|---|---|
| `habitacion_id` | `required`, `integer`, `exists:habitaciones,id` |
| `fecha_ingreso` | `required`, `date`, `after_or_equal:today` |
| `fecha_salida` | `required`, `date`, `after:fecha_ingreso` |
| `cantidad_personas` | `required`, `integer`, `min:1` |

Si eso pasa, en el controlador reviso tres cosas más, en este orden, y devuelvo el primer error que encuentre:

1. **Capacidad**: las personas no pueden ser más que la `capacidad` de la habitación.
2. **Disponibilidad**: la habitación tiene que estar `Disponible`.
3. **Fechas cruzadas**: no puede haber otra reserva **no cancelada** de la misma habitación que se cruce con esas fechas. La condición que uso es `fecha_ingreso < salida_nueva AND fecha_salida > ingreso_nuevo`.

Si todo está bien:

1. Calculo `sub_total = noches × valor de la habitación`.
2. Creo la `Reserva` (en `Pendiente`) y su `DetalleReserva` dentro de un mismo `DB::transaction`, para que no quede una sin la otra.
3. Le creo una `Notificacion` a **cada** admin (`User::administradores()`) con un mensaje tipo *"Nueva reserva de {cliente} para la habitación «{habitación}»."*
4. Lo mando a `reservas.mias` con el mensaje *"Reserva realizada correctamente. Está pendiente de confirmación."*

La reserva se queda `Pendiente` hasta que el cliente la pague (ver [MODULO_PAGOS.md](MODULO_PAGOS.md)) o un admin la confirme.

## Mis reservas: `ReservaController@misReservas`

Ruta `GET /mis-reservas` (`reservas.mias`).

1. Primero corro `Reserva::finalizarVencidas()` y `Reserva::sincronizarEstadosHabitaciones()`. Esto pasa cada vez que se carga la página, no tengo un cron ni un scheduler para eso.
2. Traigo las reservas donde `detalle.user_id` es el usuario logueado, con `habitacion`, `detalle` y `pagos` cargados de una vez.
3. La vista `resources/views/reservas/mias.blade.php` muestra una tabla con la habitación, fechas, personas, subtotal, el estado, el último pago (si hay) y los botones: **Pagar** si está `Pendiente` y **Cancelar** si está `Pendiente` o `Confirmada`.

## Cancelar: `ReservaController@cancelar`

Ruta `POST /reservas/{reserva}/cancelar` (`reservas.cancelar`).

- Solo la puede cancelar el dueño (`detalle.user_id === auth()->id()`), si no da `403`.
- Solo se puede cancelar si está `Pendiente` o `Confirmada`.
- Si ya estaba pagada, el pago no se devuelve. Como es una simulación no hice lógica de reembolsos.

## Actualización automática de las habitaciones

`Reserva::sincronizarEstadosHabitaciones()` se llama junto con `finalizarVencidas()` en `misReservas()` y en `index()`, y lo que hace es que el estado de las habitaciones cuadre con las reservas:

- Pone **`Ocupada`** las habitaciones que tienen una reserva `Confirmada` que incluye el día de hoy (`fecha_ingreso <= hoy < fecha_salida`). Si la habitación está en `Mantenimiento` o `Fuera de servicio` no la toco, porque esos estados los pone el admin a mano y tienen prioridad.
- Pone **`Disponible`** las que estaban `Ocupada` pero ya no tienen reserva para hoy.

Como esto se ejecuta cuando alguien carga la página y no con un job, puede que el estado quede un rato desfasado, pero se corrige solo la próxima vez que alguien entre a `reservas.mias` o `admin.reservas.index`.

## Reservas en el panel de admin

`ReservaController@index` (`admin.reservas.index`) hace la misma sincronización y muestra **todas** las reservas con el cliente, la habitación y el último pago. Cada fila tiene un `<select>` de estado que al cambiarlo se envía solo (`PATCH admin.reservas.cambiarEstado`), así el admin puede poner el estado que quiera sin las validaciones del cliente.

## Notificaciones

Por cada reserva nueva se crea una `Notificacion` para cada admin. En `admin.notificaciones.index` se listan de la más nueva a la más vieja, y se pueden marcar como leídas de a una (`admin.notificaciones.marcarLeida`) o todas de una vez (`admin.notificaciones.marcarTodas`). Cada admin solo puede marcar las suyas (si `notificacion.user_id !== auth()->id()` da `403`).
