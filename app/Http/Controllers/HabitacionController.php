<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHabitacionRequest;
use App\Http\Requests\UpdateHabitacionRequest;
use App\Models\Habitacion;
use App\Models\Zona;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HabitacionController extends Controller
{
    /**
     * Lista todas las habitaciones con su zona e imagen principal.
     */
    public function index(): View
    {
        $habitaciones = Habitacion::with('zona', 'imagenPrincipal')->get();

        return view('admin.habitaciones.index', compact('habitaciones'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create(): View
    {
        $zonas = Zona::all();

        return view('admin.habitaciones.create', compact('zonas'));
    }

    /**
     * Persiste la nueva habitación. El estado siempre nace como 'Disponible'.
     */
    public function store(StoreHabitacionRequest $request): RedirectResponse
    {
        Habitacion::create(array_merge($request->validated(), [
            'estado' => 'Disponible',
        ]));

        return redirect()->route('admin.habitaciones.index')
                         ->with('success', 'Habitación creada correctamente.');
    }

    /**
     * Vista pública de detalle: imágenes ordenadas por principal desc, fecha_subida asc.
     */
    public function show(Habitacion $habitacion): View
    {
        $habitacion->load([
            'zona',
            'imagenes' => fn ($q) => $q->orderByDesc('principal')->orderBy('fecha_subida'),
        ]);

        return view('habitaciones.show', compact('habitacion'));
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(Habitacion $habitacion): View
    {
        $habitacion->load('imagenes');
        $zonas = Zona::all();

        return view('admin.habitaciones.edit', compact('habitacion', 'zonas'));
    }

    /**
     * Actualiza los datos de la habitación.
     */
    public function update(UpdateHabitacionRequest $request, Habitacion $habitacion): RedirectResponse
    {
        $habitacion->update($request->validated());

        return redirect()->route('admin.habitaciones.index')
                         ->with('success', 'Habitación actualizada correctamente.');
    }

    /**
     * Marca la habitación como 'Fuera de servicio' en lugar de eliminarla,
     * preservando el historial de reservas asociadas.
     */
    public function destroy(Habitacion $habitacion): RedirectResponse
    {
        $habitacion->update(['estado' => 'Fuera de servicio']);

        return redirect()->route('admin.habitaciones.index')
                         ->with('success', 'Habitación dada de baja correctamente.');
    }
}
