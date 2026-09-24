<x-app-layout title="Factura de pago">
    <x-slot name="header">
        <div>
            <h2 class="page-title">Factura de pago</h2>
            <p class="page-subtitle">Comprobante de la simulación de pago — reserva #{{ $reserva->id }}.</p>
        </div>
    </x-slot>

    <div class="container-page py-8">
        <div class="mx-auto max-w-2xl space-y-6">

            @if (session('success'))
                <div class="alert alert-success print:hidden">{{ session('success') }}</div>
            @endif

            <div class="card card-pad" id="factura">
                {{-- Encabezado --}}
                <div class="flex items-start justify-between gap-4 border-b border-brand-100 pb-4">
                    <div>
                        <p class="font-display text-lg font-bold text-brand-900">{{ config('app.name') }}</p>
                        <p class="text-xs text-brand-400">Factura simulada — no constituye un cobro real</p>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-green">{{ $pago->estado_pago }}</span>
                        <p class="mt-1 text-xs text-brand-400">Ref. {{ $pago->referencia }}</p>
                    </div>
                </div>

                {{-- Cliente y reserva --}}
                <div class="grid gap-6 border-b border-brand-100 py-5 sm:grid-cols-2">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-brand-400">Cliente</p>
                        <p class="font-medium text-brand-900">{{ $reserva->detalle->user->name }}</p>
                        <p class="text-sm text-brand-500">{{ $reserva->detalle->user->documento }}</p>
                        <p class="text-sm text-brand-500">{{ $reserva->detalle->user->email }}</p>
                    </div>
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-brand-400">Reserva</p>
                        <p class="font-medium text-brand-900">{{ $reserva->habitacion->nombre_habitacion }}</p>
                        <p class="text-sm text-brand-500">Zona {{ $reserva->habitacion->zona->codigo_zona }}</p>
                        <p class="text-sm text-brand-500">
                            {{ $reserva->detalle->fecha_ingreso->format('d/m/Y') }}
                            &rarr;
                            {{ $reserva->detalle->fecha_salida->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                {{-- Desglose del cálculo --}}
                <div class="py-5">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-brand-400">Cómo se calculó el total</p>
                    <div class="overflow-hidden rounded-xl border border-brand-100">
                        <table class="w-full text-sm">
                            <thead class="bg-brand-50/70 text-xs uppercase tracking-wide text-brand-400">
                                <tr>
                                    <th class="px-4 py-2 text-left">Concepto</th>
                                    <th class="px-4 py-2 text-right">Valor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-100">
                                <tr>
                                    <td class="px-4 py-2 text-brand-600">Valor por noche</td>
                                    <td class="px-4 py-2 text-right text-brand-900">${{ number_format($reserva->habitacion->valor, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-brand-600">Noches ({{ $reserva->detalle->fecha_ingreso->format('d/m/Y') }} &rarr; {{ $reserva->detalle->fecha_salida->format('d/m/Y') }})</td>
                                    <td class="px-4 py-2 text-right text-brand-900">&times; {{ $noches }}</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-2 text-brand-600">Cantidad de personas</td>
                                    <td class="px-4 py-2 text-right text-brand-900">&times; {{ $reserva->detalle->cantidad_personas }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="bg-brand-50/70">
                                    <td class="px-4 py-3 font-semibold text-brand-900">
                                        Total pagado
                                        <span class="block text-xs font-normal text-brand-400">
                                            ${{ number_format($reserva->habitacion->valor, 0, ',', '.') }} &times; {{ $noches }} noches &times; {{ $reserva->detalle->cantidad_personas }} personas
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-display text-xl font-bold text-gold-600">
                                        ${{ number_format($pago->monto, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Método de pago --}}
                <div class="grid gap-1 border-t border-brand-100 pt-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-brand-400">Método de pago</span>
                        <span class="text-brand-900">
                            {{ $pago->metodo_pago }}
                            @if ($pago->tarjeta_enmascarada)
                                &middot; {{ $pago->tarjeta_enmascarada }}
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-brand-400">Fecha de pago</span>
                        <span class="text-brand-900">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-brand-400">Referencia</span>
                        <span class="text-brand-900">{{ $pago->referencia }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 print:hidden">
                <a href="{{ route('reservas.mias') }}" class="btn btn-outline">Volver a mis reservas</a>
                <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir factura</button>
            </div>
        </div>
    </div>
</x-app-layout>
