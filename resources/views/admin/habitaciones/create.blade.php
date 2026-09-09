<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Nueva habitación
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow">

                <form method="POST" action="{{ route('admin.habitaciones.store') }}">
                    @csrf

                    {{-- Zona --}}
                    <div>
                        <x-input-label for="zona_id" :value="__('Zona')" />
                        <select id="zona_id" name="zona_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Selecciona una zona --</option>
                            @foreach ($zonas as $zona)
                                <option value="{{ $zona->id }}" {{ old('zona_id') == $zona->id ? 'selected' : '' }}>
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
                                      :value="old('nombre_habitacion')" required />
                        <x-input-error :messages="$errors->get('nombre_habitacion')" class="mt-2" />
                    </div>

                    {{-- Capacidad --}}
                    <div class="mt-4">
                        <x-input-label for="capacidad" :value="__('Capacidad (personas)')" />
                        <x-text-input id="capacidad" name="capacidad" type="number"
                                      class="mt-1 block w-full" min="1"
                                      :value="old('capacidad')" required />
                        <x-input-error :messages="$errors->get('capacidad')" class="mt-2" />
                    </div>

                    {{-- Valor --}}
                    <div class="mt-4">
                        <x-input-label for="valor" :value="__('Valor por noche ($)')" />
                        <x-text-input id="valor" name="valor" type="number"
                                      class="mt-1 block w-full" min="0" step="0.01"
                                      :value="old('valor')" required />
                        <x-input-error :messages="$errors->get('valor')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-4">
                        <a href="{{ route('admin.habitaciones.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        <x-primary-button>Crear habitación</x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
