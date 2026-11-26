<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHabitacionRequest;
use App\Http\Requests\UpdateHabitacionRequest;
use App\Http\Resources\HabitacionResource;
use App\Models\Habitacion;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HabitacionController extends Controller
{
    /**
     * Lista todas las habitaciones (cualquier estado).
     */
    public function index(): AnonymousResourceCollection
    {
        Reserva::finalizarVencidas();
        Reserva::sincronizarEstadosHabitaciones();

        return HabitacionResource::collection(Habitacion::with('zona', 'imagenPrincipal')->get());
    }

    /**
     * Crea una habitación; el estado siempre nace como 'Disponible'.
     */
    public function store(StoreHabitacionRequest $request): JsonResponse
    {
        $habitacion = Habitacion::create([...$request->validated(), 'estado' => 'Disponible']);

        return (new HabitacionResource($habitacion->load('zona')))->response()->setStatusCode(201);
    }

    public function show(Habitacion $habitacion): HabitacionResource
    {
        return new HabitacionResource($habitacion->load([
            'zona',
            'imagenPrincipal',
            'imagenes' => fn ($q) => $q->orderByDesc('principal')->orderBy('fecha_subida'),
        ]));
    }

    public function update(UpdateHabitacionRequest $request, Habitacion $habitacion): HabitacionResource
    {
        $habitacion->update($request->validated());

        return new HabitacionResource($habitacion->load('zona', 'imagenPrincipal'));
    }

    /**
     * Da de baja la habitación (Fuera de servicio) en lugar de borrarla,
     * preservando el historial de reservas.
     */
    public function destroy(Habitacion $habitacion): JsonResponse
    {
        $habitacion->update(['estado' => 'Fuera de servicio']);

        return response()->json([
            'message' => 'Habitación dada de baja correctamente.',
            'data' => new HabitacionResource($habitacion),
        ]);
    }
}
