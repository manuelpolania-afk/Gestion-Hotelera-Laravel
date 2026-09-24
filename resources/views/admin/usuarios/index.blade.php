<x-app-layout title="Usuarios">
    <x-slot name="header">
        <div>
            <h2 class="page-title">Usuarios registrados</h2>
            <p class="page-subtitle">{{ $usuarios->count() }} usuarios en el sistema.</p>
        </div>
    </x-slot>

    <div class="container-page py-8">
        <div class="mx-auto max-w-6xl">

            @if ($usuarios->isEmpty())
                <div class="empty-state">No hay usuarios registrados.</div>
            @else
                <div class="table-card overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Documento</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $user)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-100 text-sm font-bold uppercase text-brand-600">
                                                {{ \Illuminate\Support\Str::substr($user->name, 0, 1) }}
                                            </span>
                                            <span class="font-medium text-brand-900">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $user->documento }}</td>
                                    <td>{{ $user->telefono ?: '—' }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if ($user->isAdmin())
                                            <span class="badge badge-blue">Administrador</span>
                                        @else
                                            <span class="badge badge-gray">Cliente</span>
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
