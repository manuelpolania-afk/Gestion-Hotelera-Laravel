<x-app-layout title="Editar habitación">
    <x-slot name="header">
        <h2 class="page-title">Editar habitación</h2>
        <p class="page-subtitle">{{ $habitacion->nombre_habitacion }}</p>
    </x-slot>

    <div class="container-page py-8">
        <div class="mx-auto max-w-3xl space-y-6">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Datos --}}
            <div class="card card-pad">
                <h3 class="mb-4 font-display text-base font-bold text-brand-900">Datos de la habitación</h3>

                <form method="POST" action="{{ route('admin.habitaciones.update', $habitacion) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="zona_id" :value="__('Zona')" />
                        <select id="zona_id" name="zona_id" class="form-select">
                            @foreach ($zonas as $zona)
                                <option value="{{ $zona->id }}" {{ old('zona_id', $habitacion->zona_id) == $zona->id ? 'selected' : '' }}>
                                    {{ $zona->codigo_zona }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('zona_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nombre_habitacion" :value="__('Nombre de habitación')" />
                        <x-text-input id="nombre_habitacion" name="nombre_habitacion" type="text"
                                      class="mt-1" maxlength="50"
                                      :value="old('nombre_habitacion', $habitacion->nombre_habitacion)" required />
                        <x-input-error :messages="$errors->get('nombre_habitacion')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="descripcion" :value="__('Descripción')" />
                        <textarea id="descripcion" name="descripcion" rows="4" maxlength="1000"
                                  class="form-textarea mt-1">{{ old('descripcion', $habitacion->descripcion) }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label for="capacidad" :value="__('Capacidad')" />
                            <x-text-input id="capacidad" name="capacidad" type="number"
                                          class="mt-1" min="1"
                                          :value="old('capacidad', $habitacion->capacidad)" required />
                            <x-input-error :messages="$errors->get('capacidad')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="valor" :value="__('Valor / noche ($)')" />
                            <x-text-input id="valor" name="valor" type="number"
                                          class="mt-1" min="0" step="0.01"
                                          :value="old('valor', $habitacion->valor)" required />
                            <x-input-error :messages="$errors->get('valor')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="estado" :value="__('Estado')" />
                            <select id="estado" name="estado" class="form-select">
                                @foreach (['Disponible', 'Ocupada', 'Mantenimiento', 'Fuera de servicio'] as $estado)
                                    <option value="{{ $estado }}" {{ old('estado', $habitacion->estado) === $estado ? 'selected' : '' }}>
                                        {{ $estado }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('admin.habitaciones.index') }}" class="btn btn-ghost">Cancelar</a>
                        <x-primary-button>Guardar cambios</x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Imágenes --}}
            <div class="card card-pad">
                <h3 class="mb-4 font-display text-base font-bold text-brand-900">Imágenes</h3>

                @if ($habitacion->imagenes->isEmpty())
                    <p class="mb-4 text-sm text-brand-400">Sin imágenes cargadas.</p>
                @else
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                        @foreach ($habitacion->imagenes as $imagen)
                            <div class="overflow-hidden rounded-xl border border-brand-100">
                                <div class="relative">
                                    <img src="{{ asset('storage/' . $imagen->ruta) }}" alt="Imagen habitación" class="h-28 w-full object-cover">
                                    @if ($imagen->principal)
                                        <span class="badge badge-blue absolute left-1.5 top-1.5">Principal</span>
                                    @endif
                                </div>
                                <div class="flex gap-1 border-t border-brand-100 p-1.5">
                                    @if (! $imagen->principal)
                                        <form method="POST" action="{{ route('admin.imagenes.principal', $imagen) }}" class="flex-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-full rounded-lg bg-brand-50 px-2 py-1 text-xs font-medium text-brand-700 hover:bg-brand-100">
                                                Hacer principal
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.imagenes.destroy', $imagen) }}" class="flex-1"
                                          onsubmit="return confirm('¿Eliminar esta imagen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full rounded-lg bg-red-50 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-100">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 border-t border-brand-100 pt-6">
                    <h4 class="mb-3 text-sm font-medium text-brand-700">Subir imágenes</h4>
                    <form method="POST" action="{{ route('admin.habitaciones.imagenes.store', $habitacion) }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="file" name="imagenes[]" multiple accept="image/*"
                               class="block w-full text-sm text-brand-500
                                      file:mr-4 file:rounded-lg file:border-0 file:bg-brand-900 file:px-4 file:py-2
                                      file:text-sm file:font-medium file:text-white hover:file:bg-brand-800">
                        <x-input-error :messages="$errors->get('imagenes')" class="mt-2" />
                        <x-input-error :messages="$errors->get('imagenes.*')" class="mt-2" />
                        <x-primary-button>Subir imágenes</x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
