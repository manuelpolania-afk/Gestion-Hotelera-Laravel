<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImagenHabitacionResource;
use App\Models\Habitacion;
use App\Models\ImagenHabitacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImagenHabitacionController extends Controller
{
    /**
     * Sube una o varias imágenes (multipart, campo "imagenes[]").
     * Si la habitación no tenía imágenes, la primera queda como principal.
     */
    public function store(Request $request, Habitacion $habitacion): JsonResponse
    {
        $request->validate([
            'imagenes' => ['required', 'array'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $sinImagenes = $habitacion->imagenes()->doesntExist();

        $creadas = collect($request->file('imagenes'))->values()->map(
            fn ($archivo, int $index) => $habitacion->imagenes()->create([
                'ruta' => $archivo->store('habitaciones', 'public'),
                'principal' => $sinImagenes && $index === 0,
            ])
        );

        return ImagenHabitacionResource::collection($creadas)->response()->setStatusCode(201);
    }

    /**
     * Marca la imagen como principal y desmarca las demás de la habitación.
     */
    public function setPrincipal(ImagenHabitacion $imagen): ImagenHabitacionResource
    {
        DB::transaction(function () use ($imagen) {
            ImagenHabitacion::where('habitacion_id', $imagen->habitacion_id)->update(['principal' => false]);
            $imagen->update(['principal' => true]);
        });

        return new ImagenHabitacionResource($imagen);
    }

    /**
     * Elimina el archivo físico y el registro.
     */
    public function destroy(ImagenHabitacion $imagen): JsonResponse
    {
        Storage::disk('public')->delete($imagen->ruta);
        $imagen->delete();

        return response()->json(['message' => 'Imagen eliminada.']);
    }
}
