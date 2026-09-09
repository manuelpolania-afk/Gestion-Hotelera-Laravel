<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Habitaciones
            </h2>
            <a href="{{ route('admin.habitaciones.create') }}"
               class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Nueva habitación
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($habitaciones->isEmpty())
                <p class="text-gray-500">No hay habitaciones registradas todavía.</p>
            @else
                <div class="overflow-hidden rounded-lg shadow">
                    <table class="min-w-full divide-y divide-gray-200 bg-white">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Imagen</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Zona</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Capacidad</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Valor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($habitaciones as $habitacion)
                                <tr>
                                    {{-- Thumbnail --}}
                                    <td class="px-4 py-3">
                                        @if ($habitacion->imagenPrincipal)
                                            <img src="{{ asset('storage/' . $habitacion->imagenPrincipal->ruta) }}"
                                                 alt="{{ $habitacion->nombre_habitacion }}"
                                                 class="h-14 w-20 rounded object-cover">
                                        @else
                                            <div class="flex h-14 w-20 items-center justify-center rounded bg-gray-100 text-xs text-gray-400">
                                                Sin imagen
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ $habitacion->nombre_habitacion }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $habitacion->zona->codigo_zona }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $habitacion->capacidad }} pers.
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        ${{ number_format($habitacion->valor, 2) }}
                                    </td>

                                    {{-- Badge de estado --}}
                                    <td class="px-4 py-3">
                                        @php
                                            $badgeClasses = match($habitacion->estado) {
                                                'Disponible'       => 'bg-green-100 text-green-800',
                                                'Ocupada'          => 'bg-red-100 text-red-800',
                                                'Mantenimiento'    => 'bg-yellow-100 text-yellow-800',
                                                'Fuera de servicio' => 'bg-gray-100 text-gray-600',
                                                default            => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeClasses }}">
                                            {{ $habitacion->estado }}
                                        </span>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="space-x-2 px-4 py-3 text-sm">
                                        <a href="{{ route('habitaciones.show', $habitacion) }}"
                                           class="text-indigo-600 hover:underline">Ver</a>

                                        <a href="{{ route('admin.habitaciones.edit', $habitacion) }}"
                                           class="text-yellow-600 hover:underline">Editar</a>

                                        @if ($habitacion->estado !== 'Fuera de servicio')
                                            <form method="POST"
                                                  action="{{ route('admin.habitaciones.destroy', $habitacion) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('¿Dar de baja esta habitación?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">
                                                    Dar de baja
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
