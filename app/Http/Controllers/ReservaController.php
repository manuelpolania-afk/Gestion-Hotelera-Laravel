<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservaRequest;
use App\Models\Habitacion;
use App\Models\Notificacion;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReservaController extends Controller
{
    /**
     * Crea una nueva reserva con su detalle dentro de una transacción.
     */
    public function store(StoreReservaRequest $request): RedirectResponse
    {
        $data          = $request->validated();
        $habitacion    = Habitacion::findOrFail($data['habitacion_id']);
        $fechaIngreso  = $data['fecha_ingreso'];
        $fechaSalida   = $data['fecha_salida'];
        $personas      = (int) $data['cantidad_personas'];

        // Verificar capacidad
        if ($personas > $habitacion->capacidad) {
            return back()->withErrors([
                'mensaje' => "La habitación solo admite {$habitacion->capacidad} persona(s).",
            ])->withInput();
        }

        // Verificar que la habitación esté disponible
        if ($habitacion->estado !== 'Disponible') {
            return back()->withErrors([
                'mensaje' => "La habitación «{$habitacion->nombre_habitacion}» no está disponible actualmente.",
            ])->withInput();
        }

        // Verificar solapamiento de fechas con reservas activas
        $solapamiento = Reserva::where('habitacion_id', $habitacion->id)
            ->where('estado_reserva', '<>', 'Cancelada')
            ->whereHas('detalle', fn ($q) => $q
                ->where('fecha_ingreso', '<', $fechaSalida)
                ->where('fecha_salida', '>', $fechaIngreso)
            )
            ->exists();

        if ($solapamiento) {
            return back()->withErrors([
                'mensaje' => 'La habitación ya tiene una reserva para ese rango de fechas.',
            ])->withInput();
        }

        // Calcular subtotal
        $dias     = (int) now()->parse($fechaIngreso)->diffInDays($fechaSalida);
        $subTotal = $dias * $personas * $habitacion->valor;

        // Crear reserva y detalle en transacción
        $reserva = DB::transaction(function () use ($habitacion, $fechaIngreso, $fechaSalida, $personas, $subTotal) {
            $reserva = Reserva::create([
                'habitacion_id'  => $habitacion->id,
                'fecha'          => now()->toDateString(),
                'estado_reserva' => 'Pendiente',
                'sub_total'      => $subTotal,
            ]);

            $reserva->detalle()->create([
                'user_id'           => auth()->id(),
                'fecha_ingreso'     => $fechaIngreso,
                'fecha_salida'      => $fechaSalida,
                'cantidad_personas' => $personas,
            ]);

            return $reserva;
        });

        // Notificar a todos los administradores
        $cliente  = auth()->user();
        $mensaje  = "Nueva reserva de {$cliente->name} para la habitación «{$habitacion->nombre_habitacion}».";

        User::administradores()->get()->each(function ($admin) use ($reserva, $mensaje) {
            Notificacion::create([
                'user_id'    => $admin->id,
                'reserva_id' => $reserva->id,
                'mensaje'    => $mensaje,
                'leida'      => false,
            ]);
        });

        return redirect()->route('reservas.mias')
                         ->with('success', 'Reserva realizada correctamente. Está pendiente de confirmación.');
    }

    /**
     * Lista las reservas del usuario autenticado.
     */
    public function misReservas(): View
    {
        Reserva::finalizarVencidas();
        Reserva::sincronizarEstadosHabitaciones();

        $reservas = Reserva::with('habitacion', 'detalle', 'pagos')
            ->whereHas('detalle', fn ($q) => $q->where('user_id', auth()->id()))
            ->orderByDesc('id')
            ->get();

        return view('reservas.mias', compact('reservas'));
    }

    /**
     * Cancela una reserva propia del usuario autenticado.
     */
    public function cancelar(Reserva $reserva): RedirectResponse
    {
        // Solo el dueño de la reserva puede cancelarla
        if ($reserva->detalle?->user_id !== auth()->id()) {
            abort(403);
        }

        if (! in_array($reserva->estado_reserva, ['Pendiente', 'Confirmada'])) {
            return back()->withErrors([
                'mensaje' => 'Solo se pueden cancelar reservas Pendientes o Confirmadas.',
            ]);
        }

        $reserva->update(['estado_reserva' => 'Cancelada']);

        return redirect()->route('reservas.mias')
                         ->with('success', 'Reserva cancelada correctamente.');
    }

    /**
     * Lista todas las reservas (vista admin).
     */
    public function index(): View
    {
        Reserva::finalizarVencidas();
        Reserva::sincronizarEstadosHabitaciones();

        $reservas = Reserva::with('habitacion', 'detalle.user', 'pagos')
            ->orderByDesc('id')
            ->get();

        return view('admin.reservas.index', compact('reservas'));
    }

    /**
     * Cambia el estado de una reserva desde el panel admin.
     */
    public function cambiarEstado(Request $request, Reserva $reserva): RedirectResponse
    {
        $request->validate([
            'estado_reserva' => ['required', 'in:Pendiente,Confirmada,Cancelada,Finalizada'],
        ]);

        $reserva->update(['estado_reserva' => $request->estado_reserva]);

        return redirect()->route('admin.reservas.index')
                         ->with('success', 'Estado de reserva actualizado.');
    }
}
