<x-app-layout title="Reservas">
    <x-slot name="header">
        <div>
            <h2 class="page-title">Reservas</h2>
            <p class="page-subtitle">Gestiona el estado de las reservas del hotel.</p>
        </div>
    </x-slot>

    <div class="container-page py-8">

        @if (session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        @if ($reservas->isEmpty())
            <div class="empty-state">No hay reservas registradas todavía.</div>
        @else
            <div class="table-card overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Habitación</th>
                            <th>Ingreso</th>
                            <th>Salida</th>
                            <th>Personas</th>
                            <th>Subtotal</th>
                            <th>Pago</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reservas as $reserva)
                            <tr>
                                <td class="text-brand-400">#{{ $reserva->id }}</td>
                                <td>
                                    <span class="font-medium text-brand-900">{{ $reserva->detalle?->user?->name ?? '—' }}</span>
                                    <span class="block text-xs text-brand-400">{{ $reserva->detalle?->user?->documento ?? '' }}</span>
                                </td>
                                <td class="font-medium text-brand-900">{{ $reserva->habitacion->nombre_habitacion }}</td>
                                <td>{{ $reserva->detalle?->fecha_ingreso?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $reserva->detalle?->fecha_salida?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $reserva->detalle?->cantidad_personas ?? '—' }}</td>
                                <td class="font-medium text-brand-800">${{ number_format($reserva->sub_total, 0, ',', '.') }}</td>
                                <td>
                                    @php $ultimoPago = $reserva->pagos->sortByDesc('id')->first(); @endphp
                                    @if ($ultimoPago)
                                        @php
                                            $badgePago = match($ultimoPago->estado_pago) {
                                                'Aprobado' => 'badge-green',
                                                'Rechazado' => 'badge-red',
                                                default => 'badge-yellow',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgePago }}">{{ $ultimoPago->estado_pago }}</span>
                                        <span class="block text-xs text-brand-400">{{ $ultimoPago->referencia }}</span>
                                    @else
                                        <span class="text-brand-300">Sin pago</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.reservas.cambiarEstado', $reserva) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="estado_reserva" onchange="this.form.submit()"
                                                class="form-select w-40 py-1.5 text-xs">
                                            @foreach (['Pendiente', 'Confirmada', 'Cancelada', 'Finalizada'] as $estado)
                                                <option value="{{ $estado }}" {{ $reserva->estado_reserva === $estado ? 'selected' : '' }}>
                                                    {{ $estado }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
