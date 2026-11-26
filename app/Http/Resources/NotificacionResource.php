<?php

namespace App\Http\Resources;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notificacion
 */
class NotificacionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mensaje' => $this->mensaje,
            'leida' => $this->leida,
            'fecha' => $this->fecha?->toIso8601String(),
            'reserva' => new ReservaResource($this->whenLoaded('reserva')),
        ];
    }
}
