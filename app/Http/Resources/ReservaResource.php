<?php

namespace App\Http\Resources;

use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Reserva
 */
class ReservaResource extends JsonResource
{
    /**
     * Los datos del detalle (fechas, personas, cliente) se aplanan en la
     * reserva para que el cliente móvil no tenga que navegar relaciones.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $detalle = $this->whenLoaded('detalle', fn () => $this->detalle);

        return [
            'id' => $this->id,
            'fecha' => $this->fecha?->toDateString(),
            'estado_reserva' => $this->estado_reserva,
            'sub_total' => (float) $this->sub_total,
            'puede_pagar' => $this->estado_reserva === 'Pendiente',
            'puede_cancelar' => in_array($this->estado_reserva, ['Pendiente', 'Confirmada']),
            $this->mergeWhen($this->relationLoaded('detalle') && $detalle !== null, fn () => [
                'fecha_ingreso' => $detalle->fecha_ingreso->toDateString(),
                'fecha_salida' => $detalle->fecha_salida->toDateString(),
                'noches' => (int) $detalle->fecha_ingreso->diffInDays($detalle->fecha_salida),
                'cantidad_personas' => (int) $detalle->cantidad_personas,
                'cliente' => $this->when(
                    $detalle->relationLoaded('user'),
                    fn () => new UserResource($detalle->user),
                ),
            ]),
            'habitacion' => new HabitacionResource($this->whenLoaded('habitacion')),
            'pagos' => PagoResource::collection($this->whenLoaded('pagos')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
