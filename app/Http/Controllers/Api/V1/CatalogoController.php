<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HabitacionResource;
use App\Http\Resources\ZonaResource;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\Zona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Endpoints públicos del catálogo (no requieren token).
 */
class CatalogoController extends Controller
{
    /**
     * Lista todas las zonas, para usarlas como filtro del catálogo.
     */
    public function zonas(): AnonymousResourceCollection
    {
        return ZonaResource::collection(Zona::orderBy('codigo_zona')->get());
    }

    /**
     * Lista las habitaciones disponibles, opcionalmente filtradas por zona.
     */
    public function habitaciones(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'zona_id' => ['nullable', 'integer'],
        ]);

        Reserva::finalizarVencidas();
        Reserva::sincronizarEstadosHabitaciones();

        $habitaciones = Habitacion::with('zona', 'imagenPrincipal')
            ->disponibles()
            ->when($request->filled('zona_id'), fn ($q) => $q->where('zona_id', $request->integer('zona_id')))
            ->get();

        return HabitacionResource::collection($habitaciones);
    }

    /**
     * Datos de la portada: todas las zonas con todas sus habitaciones (para
     * dibujar la fachada con ventanas libres y ocupadas) y el precio "desde".
     */
    public function portada(): JsonResponse
    {
        Reserva::finalizarVencidas();
        Reserva::sincronizarEstadosHabitaciones();

        $fachada = Zona::with([
            'habitaciones' => fn ($habitacionesQuery) => $habitacionesQuery->with('imagenPrincipal')->orderBy('nombre_habitacion'),
        ])->get();

        return response()->json([
            'data' => [
                'precio_desde' => Habitacion::disponibles()->min('valor') !== null
                    ? (float) Habitacion::disponibles()->min('valor')
                    : null,
                'zonas' => $fachada->map(fn (Zona $zona) => [
                    ...(new ZonaResource($zona))->resolve(),
                    'habitaciones' => HabitacionResource::collection($zona->habitaciones)->resolve(),
                ]),
            ],
        ]);
    }

    /**
     * Detalle de una habitación con todas sus imágenes (principal primero).
     */
    public function habitacion(Habitacion $habitacion): HabitacionResource
    {
        Reserva::finalizarVencidas();
        Reserva::sincronizarEstadosHabitaciones();

        $habitacion->refresh()->load([
            'zona',
            'imagenPrincipal',
            'imagenes' => fn ($q) => $q->orderByDesc('principal')->orderBy('fecha_subida'),
        ]);

        return new HabitacionResource($habitacion);
    }
}
