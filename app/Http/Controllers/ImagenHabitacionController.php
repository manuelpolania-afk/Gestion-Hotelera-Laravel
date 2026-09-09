<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\ImagenHabitacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImagenHabitacionController extends Controller
{
    /**
     * Sube una o varias imágenes para una habitación.
     */
    public function store(Request $request, Habitacion $habitacion): RedirectResponse
    {
        $request->validate([
            'imagenes'   => ['required', 'array'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        // Si la habitación no tiene imágenes, la primera que se suba será la principal
        $sinImagenes = $habitacion->imagenes()->count() === 0;

        foreach ($request->file('imagenes') as $index => $archivo) {
            $ruta = $archivo->store('habitaciones', 'public');

            $habitacion->imagenes()->create([
                'ruta'      => $ruta,
                'principal' => $sinImagenes && $index === 0,
            ]);
        }

        return redirect()
            ->route('admin.habitaciones.edit', $habitacion)
            ->with('success', 'Imágenes subidas correctamente.');
    }

    /**
     * Marca una imagen como principal y desmarca las demás de la misma habitación.
     */
    public function setPrincipal(ImagenHabitacion $imagen): RedirectResponse
    {
        DB::transaction(function () use ($imagen) {
            // Desmarcar todas las imágenes de la habitación
            ImagenHabitacion::where('habitacion_id', $imagen->habitacion_id)
                ->update(['principal' => false]);

            // Marcar la seleccionada
            $imagen->update(['principal' => true]);
        });

        return redirect()
            ->route('admin.habitaciones.edit', $imagen->habitacion_id)
            ->with('success', 'Imagen principal actualizada.');
    }

    /**
     * Elimina el archivo físico y el registro de base de datos.
     */
    public function destroy(ImagenHabitacion $imagen): RedirectResponse
    {
        $habitacionId = $imagen->habitacion_id;

        Storage::disk('public')->delete($imagen->ruta);
        $imagen->delete();

        return redirect()
            ->route('admin.habitaciones.edit', $habitacionId)
            ->with('success', 'Imagen eliminada.');
    }
}
