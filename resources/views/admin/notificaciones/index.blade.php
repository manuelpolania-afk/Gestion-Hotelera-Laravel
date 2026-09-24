<x-app-layout title="Notificaciones">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="page-title">Notificaciones</h2>
                <p class="page-subtitle">Avisos del sistema y actividad reciente.</p>
            </div>
            @if ($notificaciones->where('leida', false)->isNotEmpty())
                <form method="POST" action="{{ route('admin.notificaciones.marcarTodas') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline btn-sm">Marcar todas como leídas</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="container-page py-8">
        <div class="mx-auto max-w-3xl">

            @if (session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            @if ($notificaciones->isEmpty())
                <div class="empty-state">No tienes notificaciones.</div>
            @else
                <div class="space-y-3">
                    @foreach ($notificaciones as $notificacion)
                        <div class="card flex items-start justify-between gap-4 p-4 {{ $notificacion->leida ? '' : 'border-gold-300 bg-gold-50' }}">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $notificacion->leida ? 'bg-brand-100 text-brand-400' : 'bg-gold-400 text-brand-900' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.85 23.85 0 0 0 5.454-1.31A8.97 8.97 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.97 8.97 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.26 24.26 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                                </span>
                                <div>
                                    <p class="text-sm {{ $notificacion->leida ? 'text-brand-600' : 'font-semibold text-brand-900' }}">
                                        {{ $notificacion->mensaje }}
                                    </p>
                                    <p class="mt-1 text-xs text-brand-400">{{ $notificacion->fecha->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>

                            @if (! $notificacion->leida)
                                <form method="POST" action="{{ route('admin.notificaciones.marcarLeida', $notificacion) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-dark btn-sm shrink-0">Marcar leída</button>
                                </form>
                            @else
                                <span class="shrink-0 text-xs text-brand-300">Leída</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
