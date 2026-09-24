<x-app-layout title="Nueva zona">
    <x-slot name="header">
        <h2 class="page-title">Nueva zona</h2>
    </x-slot>

    <div class="container-page py-8">
        <div class="mx-auto max-w-xl">
            <div class="card card-pad">
                <form method="POST" action="{{ route('admin.zonas.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="codigo_zona" :value="__('Código de zona')" />
                        <x-text-input id="codigo_zona" name="codigo_zona" type="text"
                                      class="mt-1" :value="old('codigo_zona')" required autofocus />
                        <x-input-error :messages="$errors->get('codigo_zona')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="descripcion" :value="__('Descripción')" />
                        <textarea id="descripcion" name="descripcion" rows="3" class="form-textarea">{{ old('descripcion') }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('admin.zonas.index') }}" class="btn btn-ghost">Cancelar</a>
                        <x-primary-button>Crear zona</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
