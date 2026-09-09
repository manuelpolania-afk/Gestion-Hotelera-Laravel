<?php

namespace App\Http\Controllers;

use App\Models\Zona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZonaController extends Controller
{
    public function index(): View
    {
        $zonas = Zona::withCount('habitaciones')->get();

        return view('admin.zonas.index', compact('zonas'));
    }

    public function create(): View
    {
        return view('admin.zonas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'codigo_zona' => ['required', 'string', 'max:255', 'unique:zonas,codigo_zona'],
            'descripcion' => ['nullable', 'string'],
        ]);

        Zona::create($request->only('codigo_zona', 'descripcion'));

        return redirect()->route('admin.zonas.index')
                         ->with('success', 'Zona creada correctamente.');
    }

    public function edit(Zona $zona): View
    {
        return view('admin.zonas.edit', compact('zona'));
    }

    public function update(Request $request, Zona $zona): RedirectResponse
    {
        $request->validate([
            'codigo_zona' => ['required', 'string', 'max:255', "unique:zonas,codigo_zona,{$zona->id}"],
            'descripcion' => ['nullable', 'string'],
        ]);

        $zona->update($request->only('codigo_zona', 'descripcion'));

        return redirect()->route('admin.zonas.index')
                         ->with('success', 'Zona actualizada correctamente.');
    }

    public function destroy(Zona $zona): RedirectResponse
    {
        if ($zona->habitaciones()->exists()) {
            return redirect()->route('admin.zonas.index')
                             ->with('error', "No se puede eliminar la zona «{$zona->codigo_zona}» porque tiene habitaciones asociadas.");
        }

        $zona->delete();

        return redirect()->route('admin.zonas.index')
                         ->with('success', 'Zona eliminada correctamente.');
    }
}
