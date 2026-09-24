<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    /**
     * Lista las notificaciones del admin autenticado, más recientes primero.
     */
    public function index(): View
    {
        $notificaciones = Notificacion::where('user_id', auth()->id())
            ->with('reserva.habitacion', 'reserva.detalle.user')
            ->orderByDesc('fecha')
            ->get();

        return view('admin.notificaciones.index', compact('notificaciones'));
    }

    /**
     * Marca una notificación concreta como leída.
     */
    public function marcarLeida(Notificacion $notificacion): RedirectResponse
    {
        if ($notificacion->user_id !== auth()->id()) {
            abort(403);
        }

        $notificacion->update(['leida' => true]);

        return redirect()->route('admin.notificaciones.index')
                         ->with('success', 'Notificación marcada como leída.');
    }

    /**
     * Marca todas las notificaciones no leídas del admin autenticado como leídas.
     */
    public function marcarTodasLeidas(): RedirectResponse
    {
        Notificacion::where('user_id', auth()->id())
            ->noLeidas()
            ->update(['leida' => true]);

        return redirect()->route('admin.notificaciones.index')
                         ->with('success', 'Todas las notificaciones marcadas como leídas.');
    }
}
