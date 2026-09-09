<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Mis Reservas
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->has('mensaje'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ $errors->first('mensaje') }}
                </div>
            @endif

            @if ($reservas->isEmpty())
                <div class="rounded-lg bg-white p-8 text-center shadow">
                    <p class="text-gray-500">No tienes reservas registradas todavía.</p>
                    <a href="{{ url('/') }}" class="mt-4 inline-block text-sm text-indigo-600 hover:underline">
                        Ver habitaciones disponibles
                    </a>
                </div>
            @else
                <div class="overflow-hidden rounded-lg shadow">
                    <table class="min-w-full divide-y divide-gray-200 bg-white">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Habitación</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Ingreso</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Salida</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Personas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Subtotal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($reservas as $reserva)
                                @php
                                    $badgeClasses = match($reserva->estado_reserva) {
                                        'Pendiente'   => 'bg-yellow-100 text-yellow-800',
                                        'Confirmada'  => 'bg-green-100 text-green-800',
                                        'Cancelada'   => 'bg-red-100 text-red-800',
                                        'Finalizada'  => 'bg-gray-100 text-gray-600',
                                        default       => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-500">#{{ $reserva->id }}</td>

                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ $reserva->habitacion->nombre_habitacion }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $reserva->detalle?->fecha_ingreso?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $reserva->detalle?->fecha_salida?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $reserva->detalle?->cantidad_personas ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        ${{ number_format($reserva->sub_total, 2) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeClasses }}">
                                            {{ $reserva->estado_reserva }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-sm">
                                        @if (in_array($reserva->estado_reserva, ['Pendiente', 'Confirmada']))
                                            <form method="POST"
                                                  action="{{ route('reservas.cancelar', $reserva) }}"
                                                  onsubmit="return confirm('¿Cancelar esta reserva?')">
                                                @csrf
                                                <button type="submit"
                                                        class="text-red-600 hover:underline">
                                                    Cancelar
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">—</span>
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
