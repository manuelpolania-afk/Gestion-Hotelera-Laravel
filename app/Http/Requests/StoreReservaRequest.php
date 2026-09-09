<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservaRequest extends FormRequest
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
            'habitacion_id'     => ['required', 'integer', 'exists:habitaciones,id'],
            'fecha_ingreso'     => ['required', 'date', 'after_or_equal:today'],
            'fecha_salida'      => ['required', 'date', 'after:fecha_ingreso'],
            'cantidad_personas' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_ingreso.after_or_equal' => 'La fecha de ingreso no puede ser anterior a hoy.',
            'fecha_salida.after'            => 'La fecha de salida debe ser posterior a la fecha de ingreso.',
            'cantidad_personas.min'         => 'Debe haber al menos 1 persona.',
        ];
    }
}
