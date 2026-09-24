<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PagoController extends Controller
{
    /**
     * Muestra el formulario de pago simulado de una reserva.
     */
    public function create(Reserva $reserva): View|RedirectResponse
    {
        $this->autorizarPropietario($reserva);

        if ($reserva->estado_reserva !== 'Pendiente') {
            return redirect()->route('reservas.mias')
                             ->with('success', 'Esta reserva no está pendiente de pago.');
        }

        $reserva->load('habitacion', 'detalle');

        return view('pagos.create', compact('reserva'));
    }

    /**
     * Procesa el pago simulado: no se conecta a ninguna pasarela real,
     * solo aprueba o rechaza la operación con reglas de prueba.
     */
    public function store(Request $request, Reserva $reserva): RedirectResponse
    {
        $this->autorizarPropietario($reserva);

        if ($reserva->estado_reserva !== 'Pendiente') {
            return redirect()->route('reservas.mias')
                             ->with('success', 'Esta reserva no está pendiente de pago.');
        }

        // El usuario puede escribir el número con espacios/guiones (como en el placeholder);
        // se normaliza a solo dígitos antes de validar.
        $request->merge([
            'numero_tarjeta' => preg_replace('/\D/', '', (string) $request->input('numero_tarjeta')),
        ]);

        $data = $request->validate([
            'metodo_pago'      => ['required', 'in:Tarjeta,PSE,Efectivo'],
            'titular'          => ['required_if:metodo_pago,Tarjeta', 'nullable', 'string', 'max:100'],
            'numero_tarjeta'   => ['required_if:metodo_pago,Tarjeta', 'nullable', 'digits_between:13,19'],
            'fecha_expiracion' => ['required_if:metodo_pago,Tarjeta', 'nullable', 'date_format:m/y', 'after:today'],
            'cvv'              => ['required_if:metodo_pago,Tarjeta', 'nullable', 'digits_between:3,4'],
        ], [], [
            'titular'          => 'nombre del titular',
            'numero_tarjeta'   => 'número de tarjeta',
            'fecha_expiracion' => 'fecha de expiración',
        ]);

        $aprobado = $this->simularAprobacion($data);

        $pago = Pago::create([
            'reserva_id'          => $reserva->id,
            'metodo_pago'         => $data['metodo_pago'],
            'estado_pago'         => $aprobado ? 'Aprobado' : 'Rechazado',
            'monto'               => $reserva->sub_total,
            'referencia'          => 'PAY-'.strtoupper(Str::random(8)),
            'tarjeta_enmascarada' => isset($data['numero_tarjeta'])
                ? '**** **** **** '.substr($data['numero_tarjeta'], -4)
                : null,
            'fecha_pago' => $aprobado ? now() : null,
        ]);

        if (! $aprobado) {
            return back()->withErrors([
                'mensaje' => 'El pago fue rechazado (simulación). Verifica los datos e inténtalo de nuevo.',
            ])->withInput($request->except(['numero_tarjeta', 'cvv']));
        }

        $reserva->update(['estado_reserva' => 'Confirmada']);

        return redirect()->route('pagos.factura', $reserva)
                         ->with('success', "Pago aprobado (simulado). Referencia {$pago->referencia}. Tu reserva quedó Confirmada.");
    }

    /**
     * Muestra la factura del último pago aprobado de la reserva, con el
     * desglose de cómo se calculó el precio final.
     */
    public function factura(Reserva $reserva): View
    {
        $this->autorizarPropietario($reserva);

        $reserva->load('habitacion.zona', 'detalle.user');
        $pago = $reserva->pagos()->where('estado_pago', 'Aprobado')->latest()->first();

        if (! $pago) {
            abort(404, 'Esta reserva todavía no tiene un pago aprobado.');
        }

        $noches = (int) $reserva->detalle->fecha_ingreso->diffInDays($reserva->detalle->fecha_salida);

        return view('pagos.factura', compact('reserva', 'pago', 'noches'));
    }

    /**
     * Simula la respuesta de una pasarela: la tarjeta terminada en 0000 siempre
     * se rechaza (caso de prueba); el resto de métodos se aprueban con un 90%
     * de probabilidad para imitar el comportamiento real sin cobrar nada.
     */
    private function simularAprobacion(array $data): bool
    {
        if (($data['metodo_pago'] ?? null) === 'Tarjeta' && str_ends_with($data['numero_tarjeta'] ?? '', '0000')) {
            return false;
        }

        return random_int(1, 100) <= 90;
    }

    private function autorizarPropietario(Reserva $reserva): void
    {
        if ($reserva->detalle?->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
