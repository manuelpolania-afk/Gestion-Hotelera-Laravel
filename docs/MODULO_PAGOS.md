# Módulo de pagos (simulado)

> ⚠️ **Este módulo no cobra dinero real.** No se integra con ninguna pasarela de pago (Stripe, PayU, Wompi, PSE real, etc.). Es una **simulación** construida para cumplir el requisito académico de "que haya pagos en el sistema" sin manejar datos financieros reales ni credenciales de ninguna entidad bancaria. Ningún número de tarjeta ingresado se valida contra un banco, ni se almacena en texto plano (solo se guardan los últimos 4 dígitos).

## Por qué existe como módulo aparte

En vez de marcar una reserva como "pagada" con un simple booleano, se modeló un módulo de `Pago` independiente (tabla `pagos`, modelo `Pago`) para que el proyecto se parezca a un sistema real de e-commerce/reservas: cada intento de pago queda registrado (aprobado o rechazado), con su propia referencia, método y fecha — igual que dejaría un log una pasarela real, pero sin conectarse a ninguna.

## Modelo `Pago`

| Campo | Tipo | Descripción |
|---|---|---|
| `reserva_id` | FK → `reservas.id` | Reserva que se está pagando |
| `metodo_pago` | string | `Tarjeta`, `PSE` o `Efectivo` |
| `estado_pago` | string | `Pendiente` (default, no se usa en la práctica porque el resultado se decide al instante), `Aprobado`, `Rechazado` |
| `monto` | decimal(10,2) | Copia de `reserva.sub_total` en el momento del pago |
| `referencia` | string, único | Código simulado tipo `PAY-XXXXXXXX` (8 caracteres alfanuméricos aleatorios) |
| `tarjeta_enmascarada` | string, nullable | Solo si el método es `Tarjeta`: `**** **** **** 1234` |
| `fecha_pago` | timestamp, nullable | Se llena únicamente si el pago fue aprobado |

Una reserva puede acumular **varios** registros de `Pago` (uno por cada intento del cliente), lo que permite ver en el panel admin el historial completo: por ejemplo, un rechazo seguido de una aprobación.

## Flujo completo

### 1. El cliente inicia el pago

Desde "Mis reservas" (`reservas.mias`), cualquier reserva en estado `Pendiente` muestra un enlace **Pagar** que va a `GET /reservas/{reserva}/pago` (`pagos.create`, `PagoController@create`).

El controlador verifica:
- Que el usuario autenticado sea el dueño de la reserva (`detalle.user_id === auth()->id()`), si no → `403`.
- Que la reserva siga `Pendiente`; si ya fue pagada/cancelada, redirige a `reservas.mias` con un aviso.

Y muestra `resources/views/pagos/create.blade.php`: resumen de la reserva (habitación, fechas, total) + formulario de pago.

### 2. El formulario (`pagos/create.blade.php`)

Selector de método de pago (Alpine.js controla qué campos se muestran):

| Método | Campos requeridos | Comportamiento |
|---|---|---|
| **Tarjeta** | Titular, número de tarjeta, expiración (MM/AA), CVV | Se valida y se decide aprobado/rechazado (ver reglas abajo) |
| **PSE** | Ninguno adicional | Se simula aprobado al instante |
| **Efectivo** (en recepción) | Ninguno adicional | Se simula aprobado al instante |

El placeholder del número de tarjeta muestra el formato con espacios (`4111 1111 1111 1111`) a modo de guía visual; el servidor limpia automáticamente cualquier espacio/guión antes de validar, así que el usuario puede escribirlo con o sin separadores.

### 3. Validación del servidor — `PagoController@store`

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

Solo se exigen los campos de tarjeta cuando `metodo_pago === 'Tarjeta'`. La fecha de expiración debe tener formato `MM/AA` y ser posterior a hoy (una tarjeta "vencida" simulada se rechaza en la validación, antes incluso de llegar a la lógica de aprobación).

### 4. Simulación de aprobación — `simularAprobacion()`

```php
private function simularAprobacion(array $data): bool
{
    if (($data['metodo_pago'] ?? null) === 'Tarjeta' && str_ends_with($data['numero_tarjeta'] ?? '', '0000')) {
        return false;
    }
    return random_int(1, 100) <= 90;
}
```

Reglas de la simulación:

1. **Caso de prueba determinístico**: si el método es `Tarjeta` y el número termina en **`0000`**, el pago se **rechaza siempre**. Está documentado directamente en el formulario (`pagos/create.blade.php`) como pista para quien haga la demo: *"termina en 0000 para probar un rechazo"*.
2. **Resto de los casos** (cualquier otra tarjeta, PSE o Efectivo): aprobación aleatoria con **90% de probabilidad** (`random_int(1, 100) <= 90`), para imitar que en la vida real un pago puede fallar por motivos ajenos (fondos, timeout del banco, etc.) sin necesidad de una razón explícita.

### 5. Resultado

En ambos casos se crea un registro `Pago` con `referencia = 'PAY-' . strtoupper(Str::random(8))`.

- **Aprobado**: `estado_pago = 'Aprobado'`, `fecha_pago = now()`, la `Reserva` pasa a `estado_reserva = 'Confirmada'`. Redirige a `reservas.mias` con: *"Pago aprobado (simulado). Referencia PAY-XXXXXXXX. Tu reserva quedó Confirmada."*
- **Rechazado**: `estado_pago = 'Rechazado'`, `fecha_pago = null`, la reserva **permanece `Pendiente`**. Vuelve al formulario con el error *"El pago fue rechazado (simulación). Verifica los datos e inténtalo de nuevo."* — el cliente puede reintentar cuantas veces quiera (cada intento crea un nuevo registro `Pago`).

Por seguridad, si el pago es rechazado, el número de tarjeta y el CVV **no** se repueblan en el formulario (`withInput($request->except(['numero_tarjeta', 'cvv']))`) — el usuario debe volver a escribirlos.

## Visibilidad del estado del pago

- **Cliente** (`reservas/mias.blade.php`): bajo el badge de estado de la reserva se muestra el último intento de pago, p. ej. *"Pago aprobado · PAY-A1B2C3D4"*.
- **Administrador** (`admin/reservas/index.blade.php`): columna "Pago" con badge de color (`Aprobado` verde, `Rechazado` rojo, `Pendiente` amarillo) y la referencia del último intento.

## Qué NO hace este módulo (a propósito)

- No se conecta a ninguna pasarela de pago real ni API externa.
- No valida el número de tarjeta con el algoritmo de Luhn ni contra un BIN real — cualquier número de 13 a 19 dígitos es "válido" salvo la regla de prueba (`0000`).
- No guarda el número completo de tarjeta ni el CVV en base de datos (solo los últimos 4 dígitos del número).
- No genera reembolsos ni afecta el estado del pago si la reserva se cancela después de pagarse.

## Cómo demostrarlo (checklist para la sustentación)

1. Crear una reserva como cliente → queda `Pendiente`.
2. Ir a "Mis reservas" → clic en **Pagar**.
3. **Caso aprobado**: pagar con cualquier tarjeta que no termine en `0000` (ej. `4111 1111 1111 1111`) → la reserva pasa a `Confirmada` y aparece la referencia `PAY-...`.
4. **Caso rechazado**: repetir el pago con una tarjeta terminada en `0000` → mensaje de rechazo, la reserva sigue `Pendiente`, y queda un segundo registro de `Pago` con `estado_pago = 'Rechazado'`.
5. Entrar como administrador a `admin/reservas` y mostrar la columna "Pago" con el historial de ambos intentos.
