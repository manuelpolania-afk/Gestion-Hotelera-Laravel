<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ZonaResource;
use App\Models\Zona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ZonaController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ZonaResource::collection(Zona::withCount('habitaciones')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo_zona' => ['required', 'string', 'max:255', 'unique:zonas,codigo_zona'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $zona = Zona::create($data);

        return (new ZonaResource($zona))->response()->setStatusCode(201);
    }

    public function show(Zona $zona): ZonaResource
    {
        return new ZonaResource($zona->loadCount('habitaciones'));
    }

    public function update(Request $request, Zona $zona): ZonaResource
    {
        $data = $request->validate([
            'codigo_zona' => ['required', 'string', 'max:255', "unique:zonas,codigo_zona,{$zona->id}"],
            'descripcion' => ['nullable', 'string'],
        ]);

        $zona->update($data);

        return new ZonaResource($zona->loadCount('habitaciones'));
    }

    /**
     * Elimina la zona solo si no tiene habitaciones asociadas (409 en caso contrario).
     */
    public function destroy(Zona $zona): JsonResponse
    {
        if ($zona->habitaciones()->exists()) {
            return response()->json([
                'message' => "No se puede eliminar la zona «{$zona->codigo_zona}» porque tiene habitaciones asociadas.",
            ], 409);
        }

        $zona->delete();

        return response()->json(['message' => 'Zona eliminada correctamente.']);
    }
}
