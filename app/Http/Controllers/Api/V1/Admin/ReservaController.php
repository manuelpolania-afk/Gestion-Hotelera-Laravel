<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservaResource;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReservaController extends Controller
{
    /**
     * Lista todas las reservas; admite filtro opcional ?estado=Pendiente|Confirmada|Cancelada|Finalizada.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'estado' => ['nullable', 'in:Pendiente,Confirmada,Cancelada,Finalizada'],
        ]);

        Reserva::finalizarVencidas();
        Reserva::sincronizarEstadosHabitaciones();

        $reservas = Reserva::with('habitacion', 'detalle.user.role', 'pagos')
            ->when($request->filled('estado'), fn ($q) => $q->where('estado_reserva', $request->string('estado')))
            ->orderByDesc('id')
            ->get();

        return ReservaResource::collection($reservas);
    }

    public function show(Reserva $reserva): ReservaResource
    {
        return new ReservaResource($reserva->load('habitacion.zona', 'detalle.user.role', 'pagos'));
    }

    public function cambiarEstado(Request $request, Reserva $reserva): ReservaResource
    {
        $data = $request->validate([
            'estado_reserva' => ['required', 'in:Pendiente,Confirmada,Cancelada,Finalizada'],
        ]);

        $reserva->update($data);

        return new ReservaResource($reserva->load('habitacion', 'detalle.user.role', 'pagos'));
    }
}
