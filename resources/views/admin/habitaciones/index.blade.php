<x-app-layout title="Habitaciones">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="page-title">Habitaciones</h2>
                <p class="page-subtitle">{{ $habitaciones->count() }} habitaciones registradas.</p>
            </div>
            <a href="{{ route('admin.habitaciones.create') }}" class="btn btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Nueva habitación
            </a>
        </div>
    </x-slot>

    <div class="container-page py-8">

        @if (session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        @if ($habitaciones->isEmpty())
            <div class="empty-state">No hay habitaciones registradas todavía.</div>
        @else
            <div class="table-card overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Habitación</th>
                            <th>Zona</th>
                            <th>Capacidad</th>
                            <th>Valor</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($habitaciones as $habitacion)
                            @php
                                $badge = match($habitacion->estado) {
                                    'Disponible'        => 'badge-green',
                                    'Ocupada'           => 'badge-red',
                                    'Mantenimiento'     => 'badge-yellow',
                                    default             => 'badge-gray',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if ($habitacion->imagenPrincipal)
                                            <img src="{{ asset('storage/' . $habitacion->imagenPrincipal->ruta) }}"
                                                 alt="{{ $habitacion->nombre_habitacion }}"
                                                 class="h-12 w-16 rounded-lg object-cover">
                                        @else
                                            <div class="grid h-12 w-16 place-items-center rounded-lg bg-brand-100 text-brand-300">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16m-1.5-1.5 1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.91 2.91m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                                            </div>
                                        @endif
                                        <span class="font-medium text-brand-900">{{ $habitacion->nombre_habitacion }}</span>
                                    </div>
                                </td>
                                <td>{{ $habitacion->zona->codigo_zona }}</td>
                                <td>{{ $habitacion->capacidad }} pers.</td>
                                <td class="font-medium text-brand-800">${{ number_format($habitacion->valor, 0, ',', '.') }}</td>
                                <td><span class="badge {{ $badge }}">{{ $habitacion->estado }}</span></td>
                                <td>
                                    <div class="flex items-center justify-end gap-2 text-sm font-medium">
                                        <a href="{{ route('habitaciones.show', $habitacion) }}" class="btn-action-view" title="Ver habitación">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            <span>Ver</span>
                                        </a>
                                        <a href="{{ route('admin.habitaciones.edit', $habitacion) }}" class="btn-action-edit" title="Editar habitación">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                            <span>Editar</span>
                                        </a>
                                        @if ($habitacion->estado !== 'Fuera de servicio')
                                            <form method="POST" action="{{ route('admin.habitaciones.destroy', $habitacion) }}"
                                                  onsubmit="return confirm('¿Dar de baja esta habitación?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action-danger" title="Dar de baja habitación">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                    <span>Dar de baja</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="btn-action-disabled" title="Esta habitación ya se encuentra fuera de servicio">
                                                <svg class="h-3.5 w-3.5 text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                                <span>De baja</span>
                                            </span>
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
