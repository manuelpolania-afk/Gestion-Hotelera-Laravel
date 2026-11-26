<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservaRequest;
use App\Http\Resources\ReservaResource;
use App\Models\Habitacion;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reservas del cliente autenticado.
 */
class ReservaController extends Controller
{
    /**
     * Lista las reservas del usuario autenticado, más recientes primero.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Reserva::finalizarVencidas();
        Reserva::sincronizarEstadosHabitaciones();

        $reservas = Reserva::with('habitacion.zona', 'habitacion.imagenPrincipal', 'detalle', 'pagos')
            ->whereHas('detalle', fn ($q) => $q->where('user_id', $request->user()->id))
            ->orderByDesc('id')
            ->get();

        return ReservaResource::collection($reservas);
    }

    /**
     * Crea una reserva Pendiente y notifica a los administradores.
     *
     * @throws ValidationException
     */
    public function store(StoreReservaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $habitacion = Habitacion::findOrFail($data['habitacion_id']);
        $fechaIngreso = $data['fecha_ingreso'];
        $fechaSalida = $data['fecha_salida'];
        $personas = (int) $data['cantidad_personas'];

        if ($personas > $habitacion->capacidad) {
            $this->rechazar("La habitación solo admite {$habitacion->capacidad} persona(s).");
        }

        if ($habitacion->estado !== 'Disponible') {
            $this->rechazar("La habitación «{$habitacion->nombre_habitacion}» no está disponible actualmente.");
        }

        if (Reserva::haySolapamiento($habitacion->id, $fechaIngreso, $fechaSalida)) {
            $this->rechazar('La habitación ya tiene una reserva para ese rango de fechas.');
        }

        $noches = (int) now()->parse($fechaIngreso)->diffInDays($fechaSalida);
        $subTotal = $noches * $habitacion->valor;

        $reserva = DB::transaction(function () use ($request, $habitacion, $fechaIngreso, $fechaSalida, $personas, $subTotal) {
            $reserva = Reserva::create([
                'habitacion_id' => $habitacion->id,
                'fecha' => now()->toDateString(),
                'estado_reserva' => 'Pendiente',
                'sub_total' => $subTotal,
            ]);

            $reserva->detalle()->create([
                'user_id' => $request->user()->id,
                'fecha_ingreso' => $fechaIngreso,
                'fecha_salida' => $fechaSalida,
                'cantidad_personas' => $personas,
            ]);

            return $reserva;
        });

        $reserva->notificarAdministradores($request->user());

        return response()->json([
            'message' => 'Reserva realizada correctamente. Está pendiente de confirmación.',
            'data' => new ReservaResource($reserva->load('habitacion.zona', 'detalle', 'pagos')),
        ], 201);
    }

    /**
     * Muestra una reserva propia.
     */
    public function show(Request $request, Reserva $reserva): ReservaResource
    {
        $this->autorizarPropietario($request, $reserva);

        return new ReservaResource($reserva->load('habitacion.zona', 'habitacion.imagenPrincipal', 'detalle', 'pagos'));
    }

    /**
     * Cancela una reserva propia que esté Pendiente o Confirmada.
     *
     * @throws ValidationException
     */
    public function cancelar(Request $request, Reserva $reserva): JsonResponse
    {
        $this->autorizarPropietario($request, $reserva);

        if (! in_array($reserva->estado_reserva, ['Pendiente', 'Confirmada'])) {
            $this->rechazar('Solo se pueden cancelar reservas Pendientes o Confirmadas.');
        }

        $reserva->update(['estado_reserva' => 'Cancelada']);

        return response()->json([
            'message' => 'Reserva cancelada correctamente.',
            'data' => new ReservaResource($reserva->load('habitacion', 'detalle', 'pagos')),
        ]);
    }

    private function autorizarPropietario(Request $request, Reserva $reserva): void
    {
        if ($reserva->detalle?->user_id !== $request->user()->id) {
            abort(403, 'Esta reserva no te pertenece.');
        }
    }

    /**
     * @throws ValidationException
     */
    private function rechazar(string $mensaje): never
    {
        throw ValidationException::withMessages(['mensaje' => $mensaje]);
    }
}
