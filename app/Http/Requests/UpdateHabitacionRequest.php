<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHabitacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'zona_id'           => ['required', 'integer', 'exists:zonas,id'],
            'nombre_habitacion' => ['required', 'string', 'max:50'],
            'descripcion'       => ['nullable', 'string', 'max:1000'],
            'capacidad'         => ['required', 'integer', 'min:1'],
            'valor'             => ['required', 'numeric', 'min:0'],
            'estado'            => ['required', 'string', 'in:Disponible,Ocupada,Mantenimiento,Fuera de servicio'],
        ];
    }
}
