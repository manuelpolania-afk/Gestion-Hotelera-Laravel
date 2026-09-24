<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Zona;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $zonas = Zona::all();

        $query = Habitacion::with('zona', 'imagenPrincipal')
            ->where('estado', 'Disponible');

        if ($request->filled('zona_id')) {
            $query->where('zona_id', $request->zona_id);
        }

        $habitaciones = $query->get();

        return view('home.index', compact('zonas', 'habitaciones'));
    }
}
