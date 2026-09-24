# Módulo de reservas

## Modelo de datos

Una reserva se divide en dos tablas:

- **`reservas`**: cabecera — habitación, fecha de creación, estado, subtotal.
- **`detalle_reservas`** (1:1 con `reservas`): quién reservó (`user_id`), fechas de ingreso/salida y cantidad de personas.

Esta separación permite, por ejemplo, calcular el subtotal y gestionar el estado a nivel de reserva sin mezclar los datos específicos del huésped.

## Ciclo de vida (`estado_reserva`)

```
Pendiente ──(pago aprobado)──> Confirmada ──(fecha_salida pasó)──> Finalizada
    │                               │
    └──────────(cancelar)──────────┴──> Cancelada
```

| Estado | Cómo se llega | Quién puede cambiarlo |
|---|---|---|
| `Pendiente` | Estado inicial al crear la reserva | — |
| `Confirmada` | El cliente completa el pago simulado con éxito (ver [MODULO_PAGOS.md](MODULO_PAGOS.md)), o un admin la cambia manualmente | Sistema (pago aprobado) o admin |
| `Cancelada` | El cliente cancela una reserva `Pendiente` o `Confirmada`, o un admin la cambia manualmente | Cliente dueño o admin |
| `Finalizada` | Automático: se ejecuta `Reserva::finalizarVencidas()` en cada carga de "Mis reservas" o del listado admin, y pasa a `Finalizada` toda reserva `Confirmada` cuya `fecha_salida` ya pasó | Sistema |

Un admin también puede forzar cualquier estado directamente desde `admin.reservas.cambiarEstado` (select en la tabla del panel), sin las validaciones de negocio que sí aplican al flujo normal del cliente.

## Crear una reserva — `ReservaController@store`

Ruta: `POST /reservas` (`reservas.store`), autenticada, disponible para cualquier usuario logueado.

Validación (`StoreReservaRequest`):

| Campo | Regla |
|---|---|
| `habitacion_id` | `required`, `integer`, `exists:habitaciones,id` |
| `fecha_ingreso` | `required`, `date`, `after_or_equal:today` |
| `fecha_salida` | `required`, `date`, `after:fecha_ingreso` |
| `cantidad_personas` | `required`, `integer`, `min:1` |

Después de pasar la validación de formulario, el controlador aplica tres reglas de negocio adicionales, en orden, devolviendo el primer error que aplique:

1. **Capacidad**: `cantidad_personas` no puede superar `habitacion.capacidad`.
2. **Disponibilidad**: la habitación debe tener `estado === 'Disponible'`.
3. **Solapamiento de fechas**: no debe existir otra reserva **no cancelada** de la misma habitación cuyo rango de fechas se cruce con el solicitado (`fecha_ingreso < salida_nueva AND fecha_salida > ingreso_nuevo`).

Si todo pasa:

1. Calcula `sub_total = días_entre_fechas × cantidad_personas × habitacion.valor`.
2. Crea `Reserva` (`estado_reserva = 'Pendiente'`) y su `DetalleReserva` dentro de una única `DB::transaction`.
3. Crea una `Notificacion` para **cada** usuario administrador (`User::administradores()`), con un mensaje tipo *"Nueva reserva de {cliente} para la habitación «{habitación}»."*.
4. Redirige a `reservas.mias` con un mensaje de éxito: *"Reserva realizada correctamente. Está pendiente de confirmación."*

> La reserva queda `Pendiente` hasta que el cliente la paga (ver [MODULO_PAGOS.md](MODULO_PAGOS.md)) o un admin la confirma manualmente.

## "Mis reservas" — `ReservaController@misReservas`

Ruta: `GET /mis-reservas` (`reservas.mias`).

1. Ejecuta `Reserva::finalizarVencidas()` y `Reserva::sincronizarEstadosHabitaciones()` (efecto secundario en cada carga de la página, no hay un cron/scheduler dedicado).
2. Lista las reservas cuyo `detalle.user_id` es el del usuario autenticado, con `habitacion`, `detalle` y `pagos` precargados.
3. Vista `resources/views/reservas/mias.blade.php`: tabla con habitación, fechas, personas, subtotal, badge de estado, estado del último pago (si existe) y acciones — botón **Pagar** (si `Pendiente`) y **Cancelar** (si `Pendiente` o `Confirmada`).

## Cancelar — `ReservaController@cancelar`

Ruta: `POST /reservas/{reserva}/cancelar` (`reservas.cancelar`).

- Solo el dueño de la reserva puede cancelarla (`detalle.user_id === auth()->id()`, si no → `403`).
- Solo se puede cancelar si el estado actual es `Pendiente` o `Confirmada`.
- No revierte pagos ya aprobados (es una simulación académica, no hay lógica de reembolso).

## Sincronización automática de habitaciones

`Reserva::sincronizarEstadosHabitaciones()` (invocada junto con `finalizarVencidas()` en `misReservas()` e `index()`) mantiene el `estado` de `Habitacion` alineado con las reservas activas:

- Marca **`Ocupada`** toda habitación con una reserva `Confirmada` cuyo rango (`fecha_ingreso <= hoy < fecha_salida`) incluye el día de hoy — excepto si ya está en `Mantenimiento` o `Fuera de servicio` (esos estados manuales tienen prioridad).
- Marca **`Disponible`** toda habitación que estaba `Ocupada` pero ya no tiene ninguna reserva vigente hoy.

Como esto corre en cada carga de página (no hay job/scheduler), el estado de las habitaciones puede tener un desfase de segundos/minutos respecto al reloj real, pero se autocorrige en la siguiente visita a `reservas.mias` o `admin.reservas.index`.

## Panel admin de reservas

`ReservaController@index` (`admin.reservas.index`): mismo pipeline de sincronización, lista **todas** las reservas del sistema con el cliente, habitación y último pago. Cada fila tiene un `<select>` de estado que hace `PATCH admin.reservas.cambiarEstado` al cambiar (auto-submit), permitiendo al admin forzar cualquier transición de estado sin las validaciones del flujo de cliente.

## Notificaciones internas

Cada reserva nueva genera una `Notificacion` por administrador. El panel admin (`admin.notificaciones.index`) las lista ordenadas por fecha descendente, con acciones para marcar una (`admin.notificaciones.marcarLeida`) o todas (`admin.notificaciones.marcarTodas`) como leídas. Solo el propio administrador destinatario puede marcar sus notificaciones (`403` si `notificacion.user_id !== auth()->id()`).
