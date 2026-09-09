<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Editar habitación: {{ $habitacion->nombre_habitacion }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-8 px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ── Formulario de datos ── --}}
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-base font-semibold text-gray-700">Datos de la habitación</h3>

                <form method="POST" action="{{ route('admin.habitaciones.update', $habitacion) }}">
                    @csrf
                    @method('PUT')

                    {{-- Zona --}}
                    <div>
                        <x-input-label for="zona_id" :value="__('Zona')" />
                        <select id="zona_id" name="zona_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($zonas as $zona)
                                <option value="{{ $zona->id }}"
                                    {{ old('zona_id', $habitacion->zona_id) == $zona->id ? 'selected' : '' }}>
                                    {{ $zona->codigo_zona }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('zona_id')" class="mt-2" />
                    </div>

                    {{-- Nombre --}}
                    <div class="mt-4">
                        <x-input-label for="nombre_habitacion" :value="__('Nombre de habitación')" />
                        <x-text-input id="nombre_habitacion" name="nombre_habitacion" type="text"
                                      class="mt-1 block w-full" maxlength="50"
                                      :value="old('nombre_habitacion', $habitacion->nombre_habitacion)" required />
                        <x-input-error :messages="$errors->get('nombre_habitacion')" class="mt-2" />
                    </div>

                    {{-- Capacidad --}}
                    <div class="mt-4">
                        <x-input-label for="capacidad" :value="__('Capacidad (personas)')" />
                        <x-text-input id="capacidad" name="capacidad" type="number"
                                      class="mt-1 block w-full" min="1"
                                      :value="old('capacidad', $habitacion->capacidad)" required />
                        <x-input-error :messages="$errors->get('capacidad')" class="mt-2" />
                    </div>

                    {{-- Valor --}}
                    <div class="mt-4">
                        <x-input-label for="valor" :value="__('Valor por noche ($)')" />
                        <x-text-input id="valor" name="valor" type="number"
                                      class="mt-1 block w-full" min="0" step="0.01"
                                      :value="old('valor', $habitacion->valor)" required />
                        <x-input-error :messages="$errors->get('valor')" class="mt-2" />
                    </div>

                    {{-- Estado --}}
                    <div class="mt-4">
                        <x-input-label for="estado" :value="__('Estado')" />
                        <select id="estado" name="estado"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (['Disponible', 'Ocupada', 'Mantenimiento', 'Fuera de servicio'] as $estado)
                                <option value="{{ $estado }}"
                                    {{ old('estado', $habitacion->estado) === $estado ? 'selected' : '' }}>
                                    {{ $estado }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('estado')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-4">
                        <a href="{{ route('admin.habitaciones.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        <x-primary-button>Guardar cambios</x-primary-button>
                    </div>
                </form>
            </div>

            {{-- ── Galería de imágenes ── --}}
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-base font-semibold text-gray-700">Imágenes</h3>

                @if ($habitacion->imagenes->isEmpty())
                    <p class="mb-4 text-sm text-gray-400">Sin imágenes cargadas.</p>
                @else
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                        @foreach ($habitacion->imagenes as $imagen)
                            <div class="group relative overflow-hidden rounded-lg border border-gray-200">
                                <img src="{{ asset('storage/' . $imagen->ruta) }}"
                                     alt="Imagen habitación"
                                     class="h-32 w-full object-cover">

                                @if ($imagen->principal)
                                    <span class="absolute left-1 top-1 rounded bg-indigo-600 px-1.5 py-0.5 text-xs text-white">
                                        Principal
                                    </span>
                                @endif

                                <div class="flex gap-1 border-t border-gray-100 bg-white p-1">
                                    @if (! $imagen->principal)
                                        <form method="POST"
                                              action="{{ route('admin.imagenes.principal', $imagen) }}"
                                              class="flex-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="w-full rounded bg-indigo-50 px-2 py-1 text-xs text-indigo-700 hover:bg-indigo-100">
                                                Principal
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST"
                                          action="{{ route('admin.imagenes.destroy', $imagen) }}"
                                          class="flex-1"
                                          onsubmit="return confirm('¿Eliminar esta imagen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-full rounded bg-red-50 px-2 py-1 text-xs text-red-700 hover:bg-red-100">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Subida de imágenes --}}
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <h4 class="mb-3 text-sm font-medium text-gray-700">Subir imágenes</h4>
                    <form method="POST"
                          action="{{ route('admin.habitaciones.imagenes.store', $habitacion) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="imagenes[]" multiple accept="image/*"
                               class="block w-full text-sm text-gray-500
                                      file:mr-4 file:rounded-md file:border-0
                                      file:bg-indigo-50 file:px-4 file:py-2
                                      file:text-sm file:font-medium file:text-indigo-700
                                      hover:file:bg-indigo-100">
                        <x-input-error :messages="$errors->get('imagenes')" class="mt-2" />
                        <x-input-error :messages="$errors->get('imagenes.*')" class="mt-2" />
                        <div class="mt-3">
                            <x-primary-button>Subir imágenes</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
