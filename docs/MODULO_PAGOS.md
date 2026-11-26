# Módulo de pagos (simulado)

Lo primero: **este módulo no cobra plata de verdad.** No está conectado a ninguna pasarela (ni Stripe, ni PayU, ni Wompi, ni PSE real). Es una simulación que hice para cumplir con el requisito de que el sistema tuviera pagos, pero sin manejar datos financieros reales ni credenciales de ningún banco. Los números de tarjeta no se validan con ningún banco y tampoco se guardan completos, solo los últimos 4 dígitos.

## Por qué lo hice como un módulo aparte

Lo más fácil hubiera sido ponerle a la reserva un campo tipo "pagada sí/no", pero preferí hacer una tabla `pagos` con su modelo `Pago` para que se pareciera más a un sistema real. Así cada intento de pago queda guardado (aprobado o rechazado) con su referencia, el método y la fecha, como lo haría una pasarela de verdad, pero sin conectarme a ninguna.

## Modelo Pago

| Campo | Tipo | Qué guarda |
|---|---|---|
| `reserva_id` | FK a `reservas.id` | la reserva que se está pagando |
| `metodo_pago` | string | `Tarjeta`, `PSE` o `Efectivo` |
| `estado_pago` | string | `Pendiente` (es el default, pero en la práctica no se queda así porque el resultado se decide de una), `Aprobado` o `Rechazado` |
| `monto` | decimal(10,2) | copio el `sub_total` de la reserva en ese momento |
| `referencia` | string, único | un código tipo `PAY-XXXXXXXX` (8 letras/números al azar) |
| `tarjeta_enmascarada` | string, puede ser null | solo si se pagó con tarjeta: `**** **** **** 1234` |
| `fecha_pago` | timestamp, puede ser null | solo se llena si se aprobó |

Una reserva puede tener **varios** pagos, uno por cada intento. Por eso en el panel de admin se puede ver, por ejemplo, que primero lo rechazaron y después sí pasó.

## Cómo funciona paso a paso

### 1. El cliente entra a pagar

En "Mis reservas" (`reservas.mias`), las reservas que están `Pendiente` tienen un botón **Pagar** que lleva a `GET /reservas/{reserva}/pago` (`pagos.create`, `PagoController@create`).

Ahí reviso dos cosas:
- Que la reserva sea del usuario que está logueado (`detalle.user_id === auth()->id()`). Si no es suya, `403`.
- Que la reserva siga `Pendiente`. Si ya se pagó o se canceló, lo devuelvo a `reservas.mias` con un aviso.

Si todo está bien muestro `resources/views/pagos/create.blade.php`, con el resumen de la reserva (habitación, fechas, total) y el formulario.

### 2. El formulario

Hay un selector del método de pago y con Alpine.js muestro u oculto los campos según lo que se elija:

| Método | Qué pide | Qué pasa |
|---|---|---|
| **Tarjeta** | titular, número, vencimiento (MM/AA) y CVV | se valida y se decide si se aprueba o no (reglas más abajo) |
| **PSE** | nada más | se aprueba de una (simulado) |
| **Efectivo** (en recepción) | nada más | se aprueba de una (simulado) |

En el campo de la tarjeta puse de ejemplo `4111 1111 1111 1111` con espacios. El servidor le quita los espacios y guiones antes de validar, entonces se puede escribir como sea.

### 3. Validación en `PagoController@store`

```php
$request->merge(['numero_tarjeta' => preg_replace('/\D/', '', (string) $request->input('numero_tarjeta'))]);

$data = $request->validate([
    'metodo_pago'      => ['required', 'in:Tarjeta,PSE,Efectivo'],
    'titular'          => ['required_if:metodo_pago,Tarjeta', 'nullable', 'string', 'max:100'],
    'numero_tarjeta'   => ['required_if:metodo_pago,Tarjeta', 'nullable', 'digits_between:13,19'],
    'fecha_expiracion' => ['required_if:metodo_pago,Tarjeta', 'nullable', 'date_format:m/y', 'after:today'],
    'cvv'              => ['required_if:metodo_pago,Tarjeta', 'nullable', 'digits_between:3,4'],
]);
```

Los campos de tarjeta solo son obligatorios si el método es `Tarjeta`. La fecha de vencimiento tiene que ir en `MM/AA` y ser después de hoy, entonces una tarjeta vencida ni siquiera llega a la parte de aprobar, se cae en la validación.

### 4. La simulación: `simularAprobacion()`

```php
private function simularAprobacion(array $data): bool
{
    if (($data['metodo_pago'] ?? null) === 'Tarjeta' && str_ends_with($data['numero_tarjeta'] ?? '', '0000')) {
        return false;
    }
    return random_int(1, 100) <= 90;
}
```

Las reglas que le puse:

1. **Para probar un rechazo**: si es con tarjeta y el número termina en **`0000`**, siempre se rechaza. Esto lo dejé escrito en el mismo formulario como pista para la demo: *"termina en 0000 para probar un rechazo"*.
2. **Todo lo demás** (otra tarjeta, PSE o efectivo): se aprueba el **90% de las veces** (`random_int(1, 100) <= 90`). La idea es imitar que en la vida real un pago a veces falla por cosas como fondos o que el banco no responde.

### 5. Qué pasa después

En los dos casos se crea un `Pago` con `referencia = 'PAY-' . strtoupper(Str::random(8))`.

- **Si se aprueba**: `estado_pago = 'Aprobado'`, `fecha_pago = now()` y la reserva pasa a `Confirmada`. Lo mando a `reservas.mias` con el mensaje *"Pago aprobado (simulado). Referencia PAY-XXXXXXXX. Tu reserva quedó Confirmada."*
- **Si se rechaza**: `estado_pago = 'Rechazado'`, `fecha_pago = null` y la reserva **sigue `Pendiente`**. Vuelve al formulario con *"El pago fue rechazado (simulación). Verifica los datos e inténtalo de nuevo."* Puede intentar las veces que quiera y cada intento queda como un `Pago` nuevo.

Cuando se rechaza, el número de tarjeta y el CVV **no** se vuelven a llenar en el formulario (`withInput($request->except(['numero_tarjeta', 'cvv']))`), toca escribirlos otra vez. Lo hice por seguridad.

## Dónde se ve el estado del pago

- **Cliente** (`reservas/mias.blade.php`): debajo del estado de la reserva sale el último intento, por ejemplo *"Pago aprobado · PAY-A1B2C3D4"*.
- **Admin** (`admin/reservas/index.blade.php`): hay una columna "Pago" con un badge de color (verde `Aprobado`, rojo `Rechazado`, amarillo `Pendiente`) y la referencia del último intento.

## Lo que este módulo no hace (y es a propósito)

- No se conecta a ninguna pasarela ni API externa.
- No valida la tarjeta con el algoritmo de Luhn ni con BIN reales. Cualquier número de 13 a 19 dígitos pasa, menos los que terminan en `0000`.
- No guarda el número completo ni el CVV en la base de datos, solo los últimos 4 dígitos.
- No hace reembolsos. Si se cancela una reserva que ya estaba pagada, el pago queda igual.

## Cómo lo muestro en la sustentación

1. Hago una reserva como cliente, queda `Pendiente`.
2. Voy a "Mis reservas" y le doy **Pagar**.
3. **Aprobado**: pago con una tarjeta que no termine en `0000` (por ejemplo `4111 1111 1111 1111`). La reserva pasa a `Confirmada` y sale la referencia `PAY-...`.
4. **Rechazado**: hago otra reserva y pago con una tarjeta que termine en `0000`. Sale el mensaje de rechazo, la reserva sigue `Pendiente` y queda guardado un `Pago` con `estado_pago = 'Rechazado'`. Si después pago bien, esa misma reserva queda con los dos intentos.
5. Entro como admin a `admin/reservas` y muestro la columna "Pago" con el historial.
