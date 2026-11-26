@php
    $zonaActiva = request('zona_id');
    $libres = $fachada->flatMap(fn ($zona) => $zona->habitaciones->where('estado', 'Disponible')->map(fn ($habitacion) => [$habitacion, $zona]));
    $libresVisibles = $zonaActiva ? $libres->filter(fn ($par) => $par[1]->id == $zonaActiva) : $libres;
    $totalHabitaciones = $fachada->sum(fn ($zona) => $zona->habitaciones->count());
    $precio = fn ($valor) => '$' . number_format($valor, 0, ',', '.');

    // Medidas de la fachada dibujada (un piso por zona, una ventana por habitación)
    $pisos = $fachada->reverse()->values();
    $alturaPiso = 120;
    $techo = 130;
    $base = 130;
    $altoFachada = $techo + $pisos->count() * $alturaPiso + $base;
@endphp

<x-site-layout title="Habitaciones disponibles">

    {{-- Primera pantalla: la frase con la franja de fotografía --}}
    <section class="wrap flex min-h-[calc(100svh-72px)] flex-col justify-center pb-10 pt-6">
        <h1 class="hero-line" data-stagger>
            <span>Siga</span>
            <span class="hero-strip" data-shutter>
                <img src="{{ asset('images/ambiente/balcon.jpg') }}"
                     alt="Mesa con un florero junto a la baranda de un balcón, frente a los árboles"
                     width="2400" height="1000" fetchpriority="high">
            </span>
            <span>adelante</span>
        </h1>

        <div data-stagger class="mt-14 flex flex-col items-start gap-8 sm:mt-20 sm:flex-row sm:items-end sm:justify-between">
            <p class="step-lead max-w-[30ch] text-brand-950">
                {{ config('app.name') }}: habitaciones desde
                <strong class="font-medium">{{ $precioDesde ? $precio($precioDesde) : '—' }}</strong> la noche,
                con reserva y pago en línea.
            </p>
            <a href="#habitaciones" class="btn btn-primary btn-press min-h-[52px] px-7 text-base">
                Ver habitaciones libres
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 3a1 1 0 0 1 1 1v9.59l3.3-3.3a1 1 0 1 1 1.4 1.42l-5 5a1 1 0 0 1-1.4 0l-5-5a1 1 0 1 1 1.4-1.42L9 13.6V4a1 1 0 0 1 1-1Z"/></svg>
            </a>
        </div>
    </section>

    {{-- Habitaciones: la casa dibujada y la lista --}}
    <section id="habitaciones" class="wrap scroll-mt-24 py-24 sm:py-36" data-room-finder>
        <h2 data-stagger class="step-head max-w-[16ch] sm:pl-[3vw]">
            <span data-free-count>{{ $libresVisibles->count() }}</span>
            <span data-free-word>{{ $libresVisibles->count() === 1 ? 'habitación libre' : 'habitaciones libres' }}</span>
            para reservar
        </h2>

        <div class="mt-16 grid gap-14 lg:mt-24 lg:grid-cols-12 lg:gap-8">

            {{-- La fachada: cada ventana dorada es una habitación libre --}}
            <div class="lg:col-span-4" data-stagger>
                <figure class="rounded-[14px] bg-brand-100 p-4 sm:p-8">
                    <svg viewBox="0 0 640 {{ $altoFachada }}" class="h-auto w-full" role="img"
                         aria-label="Fachada de la casa: cada piso es una zona y cada ventana una habitación. Las ventanas doradas están libres.">
                        {{-- Techo --}}
                        <polygon points="40,{{ $techo }} 320,16 600,{{ $techo }}" fill="#0e1524"/>
                        <rect x="292" y="70" width="56" height="34" rx="4" fill="#f5b301"/>

                        {{-- Cuerpo --}}
                        <rect x="72" y="{{ $techo }}" width="496" height="{{ $pisos->count() * $alturaPiso + $base }}" fill="#1f2f49"/>

                        @foreach ($pisos as $i => $zona)
                            @php
                                $y = $techo + $i * $alturaPiso;
                                $cuartos = $zona->habitaciones;
                                $paso = $cuartos->count() > 0 ? min(104, 300 / $cuartos->count()) : 0;
                                $ancho = min(72, $paso - 16);
                            @endphp
                            <g class="facade-floor {{ $zonaActiva && $zonaActiva != $zona->id ? 'is-dimmed' : '' }}" data-floor="{{ $zona->id }}">
                                <rect x="72" y="{{ $y }}" width="496" height="{{ $alturaPiso }}" fill="{{ $i % 2 ? '#1f2f49' : '#273c60' }}"/>
                                <a href="{{ route('home', ['zona_id' => $zona->id]) }}#habitaciones" data-zone-link="{{ $zona->id }}">
                                    <text x="100" y="{{ $y + 66 }}" class="facade-floor-label" fill="#e7ecf6" font-size="22" font-family="Switzer, sans-serif" letter-spacing="-0.5">{{ $zona->codigo_zona }}</text>
                                </a>
                                @foreach ($cuartos as $j => $cuarto)
                                    @php $libre = $cuarto->estado === 'Disponible'; @endphp
                                    <rect x="{{ 250 + $j * $paso }}" y="{{ $y + 20 }}" width="{{ $ancho }}" height="80" rx="6"
                                          class="facade-window {{ $libre ? 'is-free' : 'is-taken' }}"
                                          tabindex="0" role="button"
                                          data-room="{{ $cuarto->id }}"
                                          data-room-label="{{ $cuarto->nombre_habitacion }} · {{ $zona->codigo_zona }} · {{ $cuarto->capacidad }} {{ $cuarto->capacidad == 1 ? 'persona' : 'personas' }} · {{ $libre ? $precio($cuarto->valor).' la noche' : $cuarto->estado }}"
                                          data-room-url="{{ $libre ? route('habitaciones.show', $cuarto) : '' }}">
                                        <title>{{ $cuarto->nombre_habitacion }} — {{ $libre ? 'libre' : $cuarto->estado }}</title>
                                    </rect>
                                @endforeach
                            </g>
                        @endforeach

                        {{-- Planta baja: recepción --}}
                        @php $yBase = $techo + $pisos->count() * $alturaPiso; @endphp
                        <rect x="72" y="{{ $yBase }}" width="496" height="{{ $base }}" fill="#182135"/>
                        <rect x="272" y="{{ $yBase + 30 }}" width="96" height="100" rx="48" ry="48" fill="#f5b301"/>
                        <rect x="272" y="{{ $yBase + 80 }}" width="96" height="50" fill="#f5b301"/>
                        <text x="100" y="{{ $yBase + 74 }}" fill="#9db3d3" font-size="20" font-family="Switzer, sans-serif">Recepción</text>
                        <rect x="0" y="{{ $altoFachada - 6 }}" width="640" height="6" fill="#0e1524"/>
                    </svg>

                    <figcaption class="mt-5 min-h-[3.2em] text-base text-brand-800" data-facade-caption aria-live="polite">
                        Toque una ventana dorada para ver la habitación, o un piso para filtrar por zona.
                    </figcaption>
                </figure>
            </div>

            {{-- La lista --}}
            <div class="lg:col-span-8 lg:pl-12" data-stagger>
                <nav class="flex flex-wrap gap-2" aria-label="Filtrar por zona">
                    <a href="{{ route('home') }}#habitaciones" data-zone-link=""
                       class="zone-chip {{ ! $zonaActiva ? 'is-active' : '' }}">Todas</a>
                    @foreach ($zonas as $zona)
                        <a href="{{ route('home', ['zona_id' => $zona->id]) }}#habitaciones" data-zone-link="{{ $zona->id }}"
                           class="zone-chip {{ $zonaActiva == $zona->id ? 'is-active' : '' }}">{{ $zona->codigo_zona }}</a>
                    @endforeach
                </nav>

                <div class="mt-10 sm:mt-14" data-room-list>
                    @foreach ($libres as [$habitacion, $zona])
                        <a href="{{ route('habitaciones.show', $habitacion) }}"
                           class="room-row {{ $zonaActiva && $zonaActiva != $zona->id ? 'is-hidden' : '' }}"
                           data-zona="{{ $zona->id }}" data-room-row="{{ $habitacion->id }}">
                            <span class="text-[1.75rem] font-medium leading-none tracking-[-0.03em] text-brand-950">
                                {{ $habitacion->nombre_habitacion }}
                            </span>
                            <span class="col-span-2 row-start-2 text-base text-brand-700 sm:col-span-1 sm:row-start-auto">
                                {{ $zona->descripcion ?: $zona->codigo_zona }}
                                @if ($habitacion->descripcion)
                                    <span class="mt-1 block text-brand-600">{{ \Illuminate\Support\Str::limit($habitacion->descripcion, 70) }}</span>
                                @endif
                            </span>
                            <span class="hidden text-base text-brand-700 sm:block">
                                {{ $habitacion->capacidad }} {{ $habitacion->capacidad == 1 ? 'persona' : 'personas' }}
                            </span>
                            <span class="col-start-2 row-start-1 flex items-baseline gap-3 justify-self-end text-lg text-brand-950 sm:col-start-auto sm:row-start-auto">
                                {{ $precio($habitacion->valor) }}
                                <svg class="room-arrow h-4 w-4 text-gold-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 10a1 1 0 0 1 1-1h9.59l-3.3-3.3a1 1 0 1 1 1.42-1.4l5 5a1 1 0 0 1 0 1.4l-5 5a1 1 0 1 1-1.42-1.4l3.3-3.3H4a1 1 0 0 1-1-1Z"/></svg>
                            </span>
                        </a>
                    @endforeach

                    <p data-room-empty class="{{ $libresVisibles->isEmpty() ? '' : 'hidden' }} rounded-[14px] bg-brand-100 px-6 py-10 text-lg text-brand-800">
                        No hay habitaciones libres en esta zona por ahora.
                        <a href="{{ route('home') }}#habitaciones" data-zone-link="" class="link-under">Ver todas las zonas</a>.
                    </p>
                </div>

                <p class="step-small mt-6 text-brand-600">Precios por noche, en pesos colombianos.</p>
            </div>
        </div>
    </section>

    {{-- La casa: foto ancha, bloque de color y datos --}}
    <section class="wrap py-24 sm:py-36">
        <div data-stagger>
            <h2 class="step-head max-w-[22ch]">Pocas habitaciones, trato de casa</h2>
            <p class="step-lead mt-6 max-w-[40ch] text-brand-900">
                {{ config('app.name') }} tiene {{ $totalHabitaciones }} habitaciones repartidas en {{ $fachada->count() }} zonas.
                Cada una muestra su capacidad y su precio, y usted la reserva y la paga sin llamar.
            </p>
        </div>

        <div class="mt-16 grid gap-4 lg:mt-20 lg:grid-cols-12" data-stagger>
            <figure class="relative overflow-hidden rounded-[14px] lg:col-span-8">
                <img src="{{ asset('images/ambiente/comedor.jpg') }}" data-drift
                     alt="Mesa de madera con sillas blancas junto a ventanales"
                     width="1920" height="1200" loading="lazy"
                     class="aspect-[4/3] w-full object-cover sm:aspect-[16/10]">
                <figcaption class="bg-paper p-5 text-base text-brand-900 sm:absolute sm:right-0 sm:top-0 sm:w-[320px] sm:rounded-bl-[14px]">
                    Las fotografías de ambiente acompañan mientras subimos las de cada habitación;
                    en la página de cada una verá sus propias fotos en cuanto estén listas.
                </figcaption>
            </figure>

            <div class="flex flex-col gap-8 lg:col-span-4">
                <div class="flex aspect-[3/2] flex-col justify-between rounded-[14px] bg-gold-400 p-6 text-brand-950 lg:aspect-auto lg:h-[60%]">
                    <p class="text-lg">Habitaciones desde</p>
                    <p>
                        <span class="block text-[clamp(2.75rem,5vw,4.5rem)] font-medium leading-none tracking-[-0.05em]" data-count-to="{{ (int) $precioDesde }}" data-count-prefix="$">
                            {{ $precioDesde ? $precio($precioDesde) : '—' }}
                        </span>
                        <span class="mt-2 block text-lg">la noche</span>
                    </p>
                </div>

                <dl class="grid grid-cols-2 gap-x-6 gap-y-5 text-base">
                    <div>
                        <dt class="text-brand-600">Habitaciones</dt>
                        <dd class="text-xl text-brand-950">{{ $totalHabitaciones }}</dd>
                    </div>
                    <div>
                        <dt class="text-brand-600">Zonas</dt>
                        <dd class="text-xl text-brand-950">{{ $fachada->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-brand-600">Recepción</dt>
                        <dd class="text-xl text-brand-950">24 horas</dd>
                    </div>
                    <div>
                        <dt class="text-brand-600">Pago</dt>
                        <dd class="text-xl text-brand-950">Tarjeta, PSE o efectivo</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    {{-- Cómo se reserva: fila de momentos --}}
    @php
        $momentos = [
            ['Elija su habitación', 'Mire la fachada o la lista, compare capacidad y precio, y abra la que le guste.', 'cama.jpg', 'Taza verde sobre una mesa de noche junto a sábanas blancas', 'h-24'],
            ['Ponga las fechas', 'Indique cuántas personas llegan, el día de entrada y el de salida. Verá el total por noches.', 'mesa.jpg', 'Taza de café sobre una mesa larga de madera', 'h-32'],
            ['Pague en línea', 'Con tarjeta, PSE o en efectivo. Al aprobarse el pago, su reserva pasa a confirmada.', 'ventana.jpg', 'Silla de madera junto a una ventana con cortinas al atardecer', 'h-16'],
            ['Guarde su factura', 'La factura queda en Mis reservas, lista para consultar o imprimir cuando la necesite.', 'arboles.jpg', 'Ventana de muchos paneles que da a los árboles', 'h-28'],
            ['Llegue a recepción', 'Le esperamos a cualquier hora. Si cambia de planes, cancele desde su cuenta.', 'cafe.jpg', 'Tazas y tetera sobre una mesa de madera con luz cálida', 'h-20'],
        ];
    @endphp
    <section class="py-24 sm:py-36" data-moments-section>
        <div class="wrap">
            <div class="flex items-end justify-between gap-6" data-stagger>
                <h2 class="text-[clamp(1.75rem,1.4rem+1.2vw,2.25rem)] leading-tight">Así se reserva</h2>
                <div class="hidden gap-2 sm:flex">
                    <button type="button" class="square-btn" data-moments-prev aria-label="Anterior">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M17 10a1 1 0 0 1-1 1H6.41l3.3 3.3a1 1 0 0 1-1.42 1.4l-5-5a1 1 0 0 1 0-1.4l5-5a1 1 0 0 1 1.42 1.4L6.4 9H16a1 1 0 0 1 1 1Z"/></svg>
                    </button>
                    <button type="button" class="square-btn" data-moments-next aria-label="Siguiente">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 10a1 1 0 0 1 1-1h9.59l-3.3-3.3a1 1 0 1 1 1.42-1.4l5 5a1 1 0 0 1 0 1.4l-5 5a1 1 0 1 1-1.42-1.4l3.3-3.3H4a1 1 0 0 1-1-1Z"/></svg>
                    </button>
                </div>
            </div>
            <div class="mt-5 h-1.5 w-full bg-brand-200">
                <div class="h-full w-1/4 origin-left bg-brand-950" data-moments-progress></div>
            </div>
        </div>

        <ol class="moments mt-10 px-4 sm:px-7" data-moments>
            @foreach ($momentos as [$titulo, $texto, $foto, $alt, $espacio])
                <li class="w-[72vw] shrink-0 sm:w-[280px]">
                    <div data-stagger>
                        <h3 class="text-lg font-semibold tracking-[-0.02em]">{{ $titulo }}</h3>
                        <p class="mt-2 text-base text-brand-700">{{ $texto }}</p>
                    </div>
                    <div class="{{ $espacio }}"></div>
                    <img src="{{ asset('images/ambiente/'.$foto) }}" alt="{{ $alt }}" width="1600" height="2000" loading="lazy"
                         draggable="false" class="aspect-[4/5] w-full rounded-[14px] object-cover">
                </li>
            @endforeach
            <li class="w-4 shrink-0 sm:w-7" aria-hidden="true"></li>
        </ol>
    </section>

    {{-- Preguntas, junto a una foto alta --}}
    <section class="overflow-hidden bg-brand-900 text-white">
        <div class="wrap grid gap-16 pb-24 lg:grid-cols-12 lg:gap-8 lg:pb-32">
            <div class="order-2 pt-0 lg:order-1 lg:col-span-5 lg:pt-32" data-stagger>
                <h2 class="text-[clamp(1.875rem,1.5rem+1.2vw,2.75rem)] leading-tight">Antes de llegar</h2>
                <ul class="mt-6 space-y-1 text-lg text-brand-100">
                    <li>Recepción abierta las 24 horas</li>
                    <li>Reservas en línea con su cuenta</li>
                    <li><a href="mailto:reservas@hotel.test" class="underline decoration-gold-400 decoration-2 underline-offset-4 hover:text-gold-300">reservas@hotel.test</a></li>
                </ul>

                <div class="faq mt-10 space-y-5">
                    <details>
                        <summary>¿Necesito una cuenta para reservar?<span class="chev"></span></summary>
                        <p class="pb-2 text-[1.0625rem] leading-relaxed text-brand-200">Sí. Con la cuenta guardamos sus reservas, sus pagos y sus facturas. Crearla toma un minuto.</p>
                    </details>
                    <details>
                        <summary>¿Cómo puedo pagar?<span class="chev"></span></summary>
                        <p class="pb-2 text-[1.0625rem] leading-relaxed text-brand-200">Con tarjeta, PSE o en efectivo. Cuando el pago se aprueba, la reserva queda confirmada y recibe su factura.</p>
                    </details>
                    <details>
                        <summary>¿Cuánto cuesta en total?<span class="chev"></span></summary>
                        <p class="pb-2 text-[1.0625rem] leading-relaxed text-brand-200">El precio de la habitación por cada noche de su estadía. Al poner las fechas verá el total antes de confirmar.</p>
                    </details>
                    <details>
                        <summary>¿Puedo cancelar?<span class="chev"></span></summary>
                        <p class="pb-2 text-[1.0625rem] leading-relaxed text-brand-200">Sí, mientras la reserva esté pendiente o confirmada. Hágalo desde Mis reservas.</p>
                    </details>
                    <details>
                        <summary>¿Cuántas personas caben?<span class="chev"></span></summary>
                        <p class="pb-2 text-[1.0625rem] leading-relaxed text-brand-200">Cada habitación indica su capacidad. El formulario de reserva no deja pasar de ese número.</p>
                    </details>
                </div>

                <div class="mt-12 flex flex-col gap-5 sm:flex-row sm:items-center">
                    <a href="#habitaciones" class="btn btn-primary btn-press min-h-[52px] px-7 text-base">Elegir habitación</a>
                    @guest
                        <a href="{{ route('register') }}" class="text-lg underline decoration-gold-400 decoration-2 underline-offset-4 hover:text-gold-300">Crear mi cuenta</a>
                    @endguest
                </div>
            </div>

            <div class="relative order-1 lg:order-2 lg:col-span-6 lg:col-start-7" data-stagger>
                <img src="{{ asset('images/ambiente/arboles.jpg') }}" alt="Ventana de muchos paneles que da a los árboles"
                     width="1600" height="2000" loading="lazy" data-drift
                     class="aspect-[4/5] w-full rounded-b-[14px] object-cover lg:aspect-[3/4]">
                <img src="{{ asset('images/ambiente/ventana.jpg') }}" alt="Silla de madera junto a una ventana con cortinas"
                     width="1600" height="2000" loading="lazy"
                     class="absolute -bottom-10 right-4 aspect-square w-[38%] rounded-[14px] object-cover lg:-left-10 lg:right-auto lg:w-1/3">
            </div>
        </div>
    </section>

</x-site-layout>
