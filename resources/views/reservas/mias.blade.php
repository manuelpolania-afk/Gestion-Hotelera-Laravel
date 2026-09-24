<x-app-layout title="Mis reservas">
    <x-slot name="header">
        <div>
            <h2 class="page-title">Mis Reservas</h2>
            <p class="page-subtitle">Historial y estado de tus reservas.</p>
        </div>
    </x-slot>

    <div class="container-page py-8">

        @if (session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif
        @if ($errors->has('mensaje'))
            <div class="alert alert-error mb-4">{{ $errors->first('mensaje') }}</div>
        @endif

        @if ($reservas->isEmpty())
            <div class="empty-state">
                <p>Aún no tienes reservas registradas.</p>
                <a href="{{ route('home') }}" class="link mt-3 inline-block">Ver habitaciones disponibles</a>
            </div>
        @else
            <div class="table-card overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Habitación</th>
                            <th>Ingreso</th>
                            <th>Salida</th>
                            <th>Personas</th>
                            <th>Subtotal</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reservas as $reserva)
                            @php
                                $badge = match($reserva->estado_reserva) {
                                    'Pendiente'  => 'badge-yellow',
                                    'Confirmada' => 'badge-green',
                                    'Cancelada'  => 'badge-red',
                                    'Finalizada' => 'badge-gray',
                                    default      => 'badge-gray',
                                };
                            @endphp
                            <tr>
                                <td class="text-brand-400">#{{ $reserva->id }}</td>
                                <td class="font-medium text-brand-900">{{ $reserva->habitacion->nombre_habitacion }}</td>
                                <td>{{ $reserva->detalle?->fecha_ingreso?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $reserva->detalle?->fecha_salida?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $reserva->detalle?->cantidad_personas ?? '—' }}</td>
                                <td class="font-medium text-brand-800">${{ number_format($reserva->sub_total, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $badge }}">{{ $reserva->estado_reserva }}</span>
                                    @php $ultimoPago = $reserva->pagos->sortByDesc('id')->first(); @endphp
                                    @if ($ultimoPago)
                                        <span class="mt-1 block text-xs text-brand-400">
                                            Pago {{ strtolower($ultimoPago->estado_pago) }} · {{ $ultimoPago->referencia }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        @if ($reserva->estado_reserva === 'Pendiente')
                                            <a href="{{ route('pagos.create', $reserva) }}" class="text-sm font-medium text-gold-600 hover:underline">Pagar</a>
                                        @endif
                                        @if ($ultimoPago?->estado_pago === 'Aprobado')
                                            <a href="{{ route('pagos.factura', $reserva) }}" class="text-sm font-medium text-brand-600 hover:underline">Ver factura</a>
                                        @endif
                                        @if (in_array($reserva->estado_reserva, ['Pendiente', 'Confirmada']))
                                            <form method="POST" action="{{ route('reservas.cancelar', $reserva) }}"
                                                  onsubmit="return confirm('¿Cancelar esta reserva?')">
                                                @csrf
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:underline">Cancelar</button>
                                            </form>
                                        @endif
                                        @if (! in_array($reserva->estado_reserva, ['Pendiente', 'Confirmada']) && $ultimoPago?->estado_pago !== 'Aprobado')
                                            <span class="text-brand-300">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
