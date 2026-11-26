<?php

namespace App\Http\Resources;

use App\Models\ImagenHabitacion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin ImagenHabitacion
 */
class ImagenHabitacionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => Storage::disk('public')->url($this->ruta),
            'principal' => $this->principal,
            'fecha_subida' => $this->fecha_subida?->toIso8601String(),
        ];
    }
}
