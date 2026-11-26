<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\Zona;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        Reserva::finalizarVencidas();
        Reserva::sincronizarEstadosHabitaciones();

        $zonas = Zona::all();

        $query = Habitacion::with('zona', 'imagenPrincipal')
            ->where('estado', 'Disponible');

        if ($request->filled('zona_id')) {
            $query->where('zona_id', $request->zona_id);
        }

        $habitaciones = $query->get();

        // Fachada de la portada: todas las zonas con todas sus habitaciones,
        // para dibujar qué ventanas están libres y cuáles no.
        $fachada = Zona::with(['habitaciones' => fn ($habitacionesQuery) => $habitacionesQuery->orderBy('nombre_habitacion')])
            ->get();

        $precioDesde = Habitacion::where('estado', 'Disponible')->min('valor');

        return view('home.index', compact('zonas', 'habitaciones', 'fachada', 'precioDesde'));
    }
}
