<?php

namespace App\Http\Resources;

use App\Models\Zona;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Zona
 */
class ZonaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo_zona' => $this->codigo_zona,
            'descripcion' => $this->descripcion,
            'habitaciones_count' => $this->whenCounted('habitaciones'),
        ];
    }
}
