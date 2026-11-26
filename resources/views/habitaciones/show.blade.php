@php
    $precio = '$' . number_format($habitacion->valor, 0, ',', '.');
    $personas = $habitacion->capacidad == 1 ? '1 persona' : $habitacion->capacidad . ' personas';
    $estadoColor = match ($habitacion->estado) {
        'Disponible' => 'bg-emerald-100 text-emerald-800',
        'Ocupada' => 'bg-red-100 text-red-800',
        'Mantenimiento' => 'bg-amber-100 text-amber-800',
        default => 'bg-brand-100 text-brand-700',
    };
@endphp

<x-site-layout :title="$habitacion->nombre_habitacion">

    <div class="wrap pb-24 pt-6 sm:pb-36">

        <a href="{{ route('home') }}#habitaciones" class="group inline-flex min-h-[44px] items-center gap-2 text-base text-brand-700 hover:text-brand-950">
            <svg class="h-4 w-4 transition-transform group-hover:-translate-x-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M17 10a1 1 0 0 1-1 1H6.41l3.3 3.3a1 1 0 0 1-1.42 1.4l-5-5a1 1 0 0 1 0-1.4l5-5a1 1 0 0 1 1.42 1.4L6.4 9H16a1 1 0 0 1 1 1Z"/></svg>
            Todas las habitaciones
        </a>

        {{-- Nombre y precio --}}
        <header data-stagger class="mt-6 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <h1 class="text-[clamp(4rem,12vw,10rem)] font-medium leading-[0.9] tracking-[-0.05em]">
                {{ $habitacion->nombre_habitacion }}
            </h1>
            <p class="text-right sm:pb-3">
                <span class="block text-[clamp(2rem,3vw,2.75rem)] font-medium leading-none tracking-[-0.04em]">{{ $precio }}</span>
                <span class="mt-1 block text-lg text-brand-700">la noche</span>
            </p>
        </header>

        <div class="mt-12 grid gap-10 lg:grid-cols-12 lg:gap-8">

            {{-- Fotos o placa de la puerta --}}
            <div class="space-y-10 lg:col-span-7">
                @if ($habitacion->imagenes->isNotEmpty())
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($habitacion->imagenes as $imagen)
                            <div class="{{ $loop->first ? 'col-span-2' : '' }} overflow-hidden rounded-[14px]" @if ($loop->first) data-shutter @endif>
                                <img src="{{ asset('storage/' . $imagen->ruta) }}"
                                     alt="{{ $habitacion->nombre_habitacion }}, foto {{ $loop->iteration }}"
                                     class="w-full object-cover {{ $loop->first ? 'aspect-[16/10]' : 'aspect-[4/3]' }}"
                                     @unless ($loop->first) loading="lazy" @endunless>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="grid gap-3 sm:grid-cols-5">
                        <div data-shutter class="relative flex aspect-[4/5] flex-col justify-between overflow-hidden rounded-[14px] bg-brand-900 p-8 text-white sm:col-span-3 sm:aspect-auto sm:min-h-[460px]">
                            {{-- Placa de la puerta, dibujada --}}
                            <div class="flex items-center justify-between text-lg text-brand-200">
                                <span>{{ $habitacion->zona->codigo_zona }}</span>
                                <span>{{ $personas }}</span>
                            </div>
                            <div class="grid place-items-center">
                                <div class="grid aspect-square w-[62%] max-w-[260px] place-items-center rounded-full bg-gold-400 text-brand-950">
                                    <span class="text-[clamp(3rem,7vw,5.5rem)] font-medium leading-none tracking-[-0.05em]">{{ $habitacion->nombre_habitacion }}</span>
                                </div>
                            </div>
                            <p class="text-base text-brand-200">Las fotos de esta habitación llegarán pronto.</p>
                        </div>
                        <img src="{{ asset('images/ambiente/cama.jpg') }}" alt="Taza verde sobre una mesa de noche junto a sábanas blancas (foto de ambiente)"
                             width="1600" height="2000" class="aspect-[4/5] w-full rounded-[14px] object-cover sm:col-span-2 sm:aspect-auto sm:h-full">
                    </div>
                @endif

                <dl data-stagger class="grid grid-cols-2 gap-x-6 gap-y-6 sm:grid-cols-4">
                    <div>
                        <dt class="text-base text-brand-600">Capacidad</dt>
                        <dd class="mt-1 text-xl">{{ $personas }}</dd>
                    </div>
                    <div>
                        <dt class="text-base text-brand-600">Zona</dt>
                        <dd class="mt-1 text-xl">{{ $habitacion->zona->codigo_zona }}</dd>
                    </div>
                    <div>
                        <dt class="text-base text-brand-600">Ubicación</dt>
                        <dd class="mt-1 text-xl">{{ $habitacion->zona->descripcion ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-base text-brand-600">Estado</dt>
                        <dd class="mt-1"><span class="inline-flex rounded-full px-3 py-1 text-base {{ $estadoColor }}">{{ $habitacion->estado }}</span></dd>
                    </div>
                </dl>

                @if ($habitacion->descripcion)
                    <p data-stagger class="step-lead max-w-[46ch] whitespace-pre-line text-brand-900"><span>{{ $habitacion->descripcion }}</span></p>
                @endif
            </div>

            {{-- Reserva --}}
            <aside class="lg:sticky lg:top-24 lg:col-span-5 lg:self-start">
                <div data-stagger class="rounded-[14px] bg-white p-6 sm:p-8">
                    <h2 class="text-[1.75rem] leading-tight">Reservar {{ $habitacion->nombre_habitacion }}</h2>
                    <p class="mt-2 text-base text-brand-700">Ponga las fechas y verá el total antes de confirmar.</p>

                    <div class="mt-6 space-y-4">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if ($errors->has('mensaje'))
                            <div class="alert alert-error">{{ $errors->first('mensaje') }}</div>
                        @endif

                        @guest
                            <div class="rounded-[14px] bg-brand-50 p-6">
                                <p class="text-lg text-brand-900">Inicie sesión para reservar esta habitación.</p>
                                <div class="mt-5 flex flex-wrap gap-3">
                                    <a href="{{ route('login') }}" class="btn btn-dark btn-press">Iniciar sesión</a>
                                    <a href="{{ route('register') }}" class="btn btn-outline btn-press">Crear cuenta</a>
                                </div>
                            </div>
                        @else
                            @if ($habitacion->estado !== 'Disponible')
                                <div class="alert alert-warning">Esta habitación no está disponible en este momento.</div>
                            @else
                                <form method="POST" action="{{ route('reservas.store') }}" class="space-y-5"
                                      data-stay-form data-price="{{ (int) $habitacion->valor }}">
                                    @csrf
                                    <input type="hidden" name="habitacion_id" value="{{ $habitacion->id }}">

                                    <div>
                                        <label for="cantidad_personas" class="form-label text-base">
                                            Personas <span class="text-brand-600">(hasta {{ $habitacion->capacidad }})</span>
                                        </label>
                                        <input type="number" id="cantidad_personas" name="cantidad_personas"
                                               min="1" max="{{ $habitacion->capacidad }}"
                                               value="{{ old('cantidad_personas', 1) }}" required
                                               class="form-input min-h-[48px] text-base">
                                        <x-input-error :messages="$errors->get('cantidad_personas')" class="mt-1" />
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="fecha_ingreso" class="form-label text-base">Entrada</label>
                                            <input type="date" id="fecha_ingreso" name="fecha_ingreso"
                                                   min="{{ now()->toDateString() }}"
                                                   value="{{ old('fecha_ingreso') }}" required
                                                   class="form-input min-h-[48px] text-base">
                                            <x-input-error :messages="$errors->get('fecha_ingreso')" class="mt-1" />
                                        </div>
                                        <div>
                                            <label for="fecha_salida" class="form-label text-base">Salida</label>
                                            <input type="date" id="fecha_salida" name="fecha_salida"
                                                   value="{{ old('fecha_salida') }}" required
                                                   class="form-input min-h-[48px] text-base">
                                            <x-input-error :messages="$errors->get('fecha_salida')" class="mt-1" />
                                        </div>
                                    </div>

                                    <div data-stay-box class="flex items-end justify-between rounded-[14px] bg-gold-100 px-5 py-4 transition-opacity">
                                        <div>
                                            <p class="text-base text-brand-800" data-stay-nights>{{ $precio }} por noche</p>
                                            <p class="text-sm text-brand-700">Total de la estadía</p>
                                        </div>
                                        <p class="text-[2rem] font-medium leading-none tracking-[-0.04em]" data-stay-total>—</p>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-press btn-block min-h-[52px] text-base">
                                        Confirmar reserva
                                    </button>
                                    <p class="text-center text-sm text-brand-700">Después de confirmar podrá pagar con tarjeta, PSE o efectivo.</p>
                                </form>
                            @endif
                        @endguest
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-site-layout>
