<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Reservas
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($reservas->isEmpty())
                <p class="text-gray-500">No hay reservas registradas todavía.</p>
            @else
                <div class="overflow-hidden rounded-lg shadow">
                    <table class="min-w-full divide-y divide-gray-200 bg-white">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Habitación</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Ingreso</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Salida</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Personas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Subtotal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Estado</th>
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

                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $reserva->detalle?->user?->name ?? '—' }}<br>
                                        <span class="text-xs text-gray-400">{{ $reserva->detalle?->user?->documento ?? '' }}</span>
                                    </td>

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

                                    {{-- Select inline para cambiar estado --}}
                                    <td class="px-4 py-3">
                                        <form method="POST"
                                              action="{{ route('admin.reservas.cambiarEstado', $reserva) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex items-center gap-2">
                                                <select name="estado_reserva"
                                                        onchange="this.form.submit()"
                                                        class="rounded-md border-gray-300 py-1 pl-2 pr-7 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ $badgeClasses }}">
                                                    @foreach (['Pendiente', 'Confirmada', 'Cancelada', 'Finalizada'] as $estado)
                                                        <option value="{{ $estado }}"
                                                            {{ $reserva->estado_reserva === $estado ? 'selected' : '' }}>
                                                            {{ $estado }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </form>
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
