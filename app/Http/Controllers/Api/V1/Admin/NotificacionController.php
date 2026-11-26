<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificacionResource;
use App\Models\Notificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificacionController extends Controller
{
    /**
     * Notificaciones del admin autenticado, más recientes primero.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $notificaciones = Notificacion::where('user_id', $request->user()->id)
            ->with('reserva.habitacion', 'reserva.detalle.user.role')
            ->orderByDesc('fecha')
            ->get();

        return NotificacionResource::collection($notificaciones);
    }

    public function marcarLeida(Request $request, Notificacion $notificacion): NotificacionResource
    {
        if ($notificacion->user_id !== $request->user()->id) {
            abort(403);
        }

        $notificacion->update(['leida' => true]);

        return new NotificacionResource($notificacion);
    }

    public function marcarTodasLeidas(Request $request): JsonResponse
    {
        $actualizadas = Notificacion::where('user_id', $request->user()->id)
            ->noLeidas()
            ->update(['leida' => true]);

        return response()->json([
            'message' => 'Todas las notificaciones marcadas como leídas.',
            'actualizadas' => $actualizadas,
        ]);
    }
}
