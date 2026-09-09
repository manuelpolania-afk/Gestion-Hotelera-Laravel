<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $habitacion->nombre_habitacion }} — Hotel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800">

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">

        {{-- Encabezado --}}
        <div class="mb-6">
            <a href="{{ url()->previous() }}" class="text-sm text-indigo-600 hover:underline">
                &larr; Volver
            </a>
        </div>

        <div class="rounded-lg bg-white shadow">

            {{-- Galería de imágenes --}}
            @if ($habitacion->imagenes->isNotEmpty())
                <div class="grid grid-cols-2 gap-2 overflow-hidden rounded-t-lg sm:grid-cols-3">
                    @foreach ($habitacion->imagenes as $imagen)
                        <div class="{{ $loop->first ? 'col-span-2 sm:col-span-2' : '' }}">
                            <img src="{{ asset('storage/' . $imagen->ruta) }}"
                                 alt="{{ $habitacion->nombre_habitacion }}"
                                 class="h-56 w-full object-cover {{ $loop->first ? 'sm:h-72' : '' }}">
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex h-48 items-center justify-center rounded-t-lg bg-gray-100 text-gray-400">
                    Sin imágenes disponibles
                </div>
            @endif

            {{-- Información --}}
            <div class="p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ $habitacion->nombre_habitacion }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Zona: <span class="font-medium">{{ $habitacion->zona->codigo_zona }}</span>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-indigo-600">
                            ${{ number_format($habitacion->valor, 2) }}
                        </p>
                        <p class="text-xs text-gray-400">por noche</p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-4 border-t border-gray-100 pt-6 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Capacidad</p>
                        <p class="mt-1 text-lg font-semibold">{{ $habitacion->capacidad }} personas</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Estado</p>
                        <p class="mt-1 text-lg font-semibold">{{ $habitacion->estado }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
