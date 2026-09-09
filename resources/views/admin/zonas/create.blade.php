<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Nueva zona
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow">

                <form method="POST" action="{{ route('admin.zonas.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="codigo_zona" :value="__('Código de zona')" />
                        <x-text-input id="codigo_zona" name="codigo_zona" type="text"
                                      class="mt-1 block w-full"
                                      :value="old('codigo_zona')" required autofocus />
                        <x-input-error :messages="$errors->get('codigo_zona')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="descripcion" :value="__('Descripción')" />
                        <textarea id="descripcion" name="descripcion" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('descripcion') }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-4">
                        <a href="{{ route('admin.zonas.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        <x-primary-button>Crear zona</x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
