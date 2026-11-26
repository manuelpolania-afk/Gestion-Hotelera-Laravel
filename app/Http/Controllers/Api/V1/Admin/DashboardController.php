<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Models\Notificacion;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Zona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Indicadores del panel de administración.
     */
    public function __invoke(Request $request): JsonResponse
    {
        Reserva::finalizarVencidas();
        Reserva::sincronizarEstadosHabitaciones();

        return response()->json([
            'data' => [
                'total_zonas' => Zona::count(),
                'total_habitaciones' => Habitacion::count(),
                'habitaciones_disponibles' => Habitacion::disponibles()->count(),
                'total_usuarios' => User::count(),
                'reservas_pendientes' => Reserva::where('estado_reserva', 'Pendiente')->count(),
                'reservas_activas' => Reserva::whereIn('estado_reserva', ['Pendiente', 'Confirmada'])->count(),
                'notificaciones_no_leidas' => Notificacion::where('user_id', $request->user()->id)->noLeidas()->count(),
            ],
        ]);
    }
}
