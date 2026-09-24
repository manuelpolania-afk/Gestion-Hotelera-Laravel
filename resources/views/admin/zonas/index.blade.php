<x-app-layout title="Zonas">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="page-title">Zonas</h2>
                <p class="page-subtitle">Sectores del hotel donde se agrupan las habitaciones.</p>
            </div>
            <a href="{{ route('admin.zonas.create') }}" class="btn btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Nueva zona
            </a>
        </div>
    </x-slot>

    <div class="container-page py-8">
        <div class="mx-auto max-w-4xl">

            @if (session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error mb-4">{{ session('error') }}</div>
            @endif

            @if ($zonas->isEmpty())
                <div class="empty-state">No hay zonas registradas todavía.</div>
            @else
                <div class="table-card overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Habitaciones</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($zonas as $zona)
                                <tr>
                                    <td class="font-medium text-brand-900">{{ $zona->codigo_zona }}</td>
                                    <td>{{ $zona->descripcion ?: '—' }}</td>
                                    <td><span class="badge badge-blue">{{ $zona->habitaciones_count }}</span></td>
                                    <td>
                                        <div class="flex items-center justify-end gap-2 text-sm font-medium">
                                            <a href="{{ route('admin.zonas.edit', $zona) }}" class="btn-action-edit" title="Editar zona">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                                <span>Editar</span>
                                            </a>
                                            <form method="POST" action="{{ route('admin.zonas.destroy', $zona) }}"
                                                  onsubmit="return confirm('¿Eliminar la zona {{ $zona->codigo_zona }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action-danger" title="Eliminar zona">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                    <span>Eliminar</span>
                                                </button>
                                            </form>
                                        </div>
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
