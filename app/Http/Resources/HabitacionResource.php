<?php

namespace App\Http\Resources;

use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Habitacion
 */
class HabitacionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_habitacion' => $this->nombre_habitacion,
            'descripcion' => $this->descripcion,
            'capacidad' => (int) $this->capacidad,
            'valor' => (float) $this->valor,
            'estado' => $this->estado,
            'zona' => new ZonaResource($this->whenLoaded('zona')),
            'imagen_principal' => $this->whenLoaded(
                'imagenPrincipal',
                fn () => $this->imagenPrincipal ? new ImagenHabitacionResource($this->imagenPrincipal) : null,
            ),
            'imagenes' => ImagenHabitacionResource::collection($this->whenLoaded('imagenes')),
        ];
    }
}
