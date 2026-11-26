<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'documento' => $this->documento,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'estado' => $this->estado,
            'rol' => $this->role?->nombre,
            'es_admin' => $this->isAdmin(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
