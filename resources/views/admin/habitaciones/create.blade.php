<x-app-layout title="Nueva habitación">
    <x-slot name="header">
        <h2 class="page-title">Nueva habitación</h2>
    </x-slot>

    <div class="container-page py-8">
        <div class="mx-auto max-w-2xl">
            <div class="card card-pad">
                <form method="POST" action="{{ route('admin.habitaciones.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="zona_id" :value="__('Zona')" />
                        <select id="zona_id" name="zona_id" class="form-select">
                            <option value="">— Selecciona una zona —</option>
                            @foreach ($zonas as $zona)
                                <option value="{{ $zona->id }}" {{ old('zona_id') == $zona->id ? 'selected' : '' }}>
                                    {{ $zona->codigo_zona }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('zona_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nombre_habitacion" :value="__('Nombre de habitación')" />
                        <x-text-input id="nombre_habitacion" name="nombre_habitacion" type="text"
                                      class="mt-1" maxlength="50" :value="old('nombre_habitacion')" required />
                        <x-input-error :messages="$errors->get('nombre_habitacion')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="descripcion" :value="__('Descripción')" />
                        <textarea id="descripcion" name="descripcion" rows="4" maxlength="1000"
                                  class="form-textarea mt-1">{{ old('descripcion') }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="capacidad" :value="__('Capacidad (personas)')" />
                            <x-text-input id="capacidad" name="capacidad" type="number"
                                          class="mt-1" min="1" :value="old('capacidad')" required />
                            <x-input-error :messages="$errors->get('capacidad')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="valor" :value="__('Valor por noche ($)')" />
                            <x-text-input id="valor" name="valor" type="number"
                                          class="mt-1" min="0" step="0.01" :value="old('valor')" required />
                            <x-input-error :messages="$errors->get('valor')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('admin.habitaciones.index') }}" class="btn btn-ghost">Cancelar</a>
                        <x-primary-button>Crear habitación</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
