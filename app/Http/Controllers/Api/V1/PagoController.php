<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PagoResource;
use App\Http\Resources\ReservaResource;
use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Pago simulado y factura de las reservas del cliente autenticado.
 */
class PagoController extends Controller
{
    /**
     * Procesa el pago simulado de una reserva Pendiente.
     * Aprobado → 201 y la reserva pasa a Confirmada. Rechazado → 422.
     *
     * @throws ValidationException
     */
    public function store(Request $request, Reserva $reserva): JsonResponse
    {
        $this->autorizarPropietario($request, $reserva);

        if ($reserva->estado_reserva !== 'Pendiente') {
            throw ValidationException::withMessages([
                'mensaje' => 'Esta reserva no está pendiente de pago.',
            ]);
        }

        $request->merge([
            'numero_tarjeta' => preg_replace('/\D/', '', (string) $request->input('numero_tarjeta')),
        ]);

        $data = $request->validate([
            'metodo_pago' => ['required', 'in:Tarjeta,PSE,Efectivo'],
            'titular' => ['required_if:metodo_pago,Tarjeta', 'nullable', 'string', 'max:100'],
            'numero_tarjeta' => ['required_if:metodo_pago,Tarjeta', 'nullable', 'digits_between:13,19'],
            'fecha_expiracion' => ['required_if:metodo_pago,Tarjeta', 'nullable', 'date_format:m/y', 'after:today'],
            'cvv' => ['required_if:metodo_pago,Tarjeta', 'nullable', 'digits_between:3,4'],
        ], [], [
            'titular' => 'nombre del titular',
            'numero_tarjeta' => 'número de tarjeta',
            'fecha_expiracion' => 'fecha de expiración',
        ]);

        $aprobado = Pago::simularAprobacion($data['metodo_pago'], $data['numero_tarjeta'] ?? null);

        $pago = Pago::create([
            'reserva_id' => $reserva->id,
            'metodo_pago' => $data['metodo_pago'],
            'estado_pago' => $aprobado ? 'Aprobado' : 'Rechazado',
            'monto' => $reserva->sub_total,
            'referencia' => 'PAY-'.strtoupper(Str::random(8)),
            'tarjeta_enmascarada' => ! empty($data['numero_tarjeta'])
                ? '**** **** **** '.substr($data['numero_tarjeta'], -4)
                : null,
            'fecha_pago' => $aprobado ? now() : null,
        ]);

        if (! $aprobado) {
            return response()->json([
                'message' => 'El pago fue rechazado (simulación). Verifica los datos e inténtalo de nuevo.',
                'pago' => new PagoResource($pago),
            ], 422);
        }

        $reserva->update(['estado_reserva' => 'Confirmada']);

        return response()->json([
            'message' => "Pago aprobado (simulado). Referencia {$pago->referencia}. Tu reserva quedó Confirmada.",
            'pago' => new PagoResource($pago),
            'reserva' => new ReservaResource($reserva->load('habitacion', 'detalle')),
        ], 201);
    }

    /**
     * Factura del último pago aprobado de la reserva, con el desglose del precio.
     */
    public function factura(Request $request, Reserva $reserva): JsonResponse
    {
        $this->autorizarPropietario($request, $reserva);

        $reserva->load('habitacion.zona', 'detalle.user');
        $pago = $reserva->pagos()->where('estado_pago', 'Aprobado')->latest()->first();

        if (! $pago) {
            abort(404, 'Esta reserva todavía no tiene un pago aprobado.');
        }

        $noches = (int) $reserva->detalle->fecha_ingreso->diffInDays($reserva->detalle->fecha_salida);

        return response()->json([
            'data' => [
                'reserva' => new ReservaResource($reserva),
                'pago' => new PagoResource($pago),
                'desglose' => [
                    'valor_noche' => (float) $reserva->habitacion->valor,
                    'noches' => $noches,
                    'total' => (float) $reserva->sub_total,
                ],
            ],
        ]);
    }

    private function autorizarPropietario(Request $request, Reserva $reserva): void
    {
        if ($reserva->detalle?->user_id !== $request->user()->id) {
            abort(403, 'Esta reserva no te pertenece.');
        }
    }
}
