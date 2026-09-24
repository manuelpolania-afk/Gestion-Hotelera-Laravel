<x-app-layout title="Pagar reserva">
    <x-slot name="header">
        <div>
            <h2 class="page-title">Pagar reserva #{{ $reserva->id }}</h2>
            <p class="page-subtitle">Pasarela simulada — no se realiza ningún cobro real.</p>
        </div>
    </x-slot>

    <div class="container-page py-8">
        <div class="mx-auto max-w-2xl space-y-6">

            @if ($errors->has('mensaje'))
                <div class="alert alert-error">{{ $errors->first('mensaje') }}</div>
            @endif

            <div class="card card-pad">
                <h3 class="mb-3 font-display text-lg font-semibold text-brand-900">Resumen de la reserva</h3>
                <dl class="grid grid-cols-2 gap-y-2 text-sm">
                    <dt class="text-brand-400">Habitación</dt>
                    <dd class="text-right font-medium text-brand-900">{{ $reserva->habitacion->nombre_habitacion }}</dd>

                    <dt class="text-brand-400">Ingreso</dt>
                    <dd class="text-right">{{ $reserva->detalle?->fecha_ingreso?->format('d/m/Y') ?? '—' }}</dd>

                    <dt class="text-brand-400">Salida</dt>
                    <dd class="text-right">{{ $reserva->detalle?->fecha_salida?->format('d/m/Y') ?? '—' }}</dd>

                    <dt class="border-t border-brand-100 pt-2 font-medium text-brand-900">Total a pagar</dt>
                    <dd class="border-t border-brand-100 pt-2 text-right font-display text-lg font-semibold text-gold-500">
                        ${{ number_format($reserva->sub_total, 0, ',', '.') }}
                    </dd>
                </dl>
            </div>

            <div class="card card-pad">
                <form method="POST" action="{{ route('pagos.store', $reserva) }}" class="space-y-4" x-data="{ metodo: '{{ old('metodo_pago', 'Tarjeta') }}' }">
                    @csrf

                    <div>
                        <x-input-label for="metodo_pago" :value="__('Método de pago')" />
                        <select id="metodo_pago" name="metodo_pago" class="form-select" x-model="metodo">
                            <option value="Tarjeta" {{ old('metodo_pago', 'Tarjeta') === 'Tarjeta' ? 'selected' : '' }}>Tarjeta de crédito/débito</option>
                            <option value="PSE" {{ old('metodo_pago') === 'PSE' ? 'selected' : '' }}>PSE</option>
                            <option value="Efectivo" {{ old('metodo_pago') === 'Efectivo' ? 'selected' : '' }}>Efectivo en recepción</option>
                        </select>
                        <x-input-error :messages="$errors->get('metodo_pago')" class="mt-2" />
                    </div>

                    <div x-show="metodo === 'Tarjeta'" class="space-y-4">
                        <div>
                            <x-input-label for="titular" :value="__('Nombre del titular')" />
                            <x-text-input id="titular" name="titular" type="text" class="mt-1"
                                          maxlength="100" :value="old('titular')" />
                            <x-input-error :messages="$errors->get('titular')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="numero_tarjeta" :value="__('Número de tarjeta')" />
                            <x-text-input id="numero_tarjeta" name="numero_tarjeta" type="text" inputmode="numeric"
                                          class="mt-1" maxlength="19" placeholder="4111 1111 1111 1111" autocomplete="off" />
                            <p class="form-hint">Simulación: cualquier número funciona; termina en <strong>0000</strong> para probar un rechazo.</p>
                            <x-input-error :messages="$errors->get('numero_tarjeta')" class="mt-2" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="fecha_expiracion" :value="__('Expiración (MM/AA)')" />
                                <x-text-input id="fecha_expiracion" name="fecha_expiracion" type="text"
                                              class="mt-1" maxlength="5" placeholder="12/28" :value="old('fecha_expiracion')" />
                                <x-input-error :messages="$errors->get('fecha_expiracion')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="cvv" :value="__('CVV')" />
                                <x-text-input id="cvv" name="cvv" type="text" inputmode="numeric"
                                              class="mt-1" maxlength="4" placeholder="123" autocomplete="off" />
                                <x-input-error :messages="$errors->get('cvv')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div x-show="metodo !== 'Tarjeta'" class="alert alert-info">
                        Este método se simula como aprobado al instante, sin datos adicionales.
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('reservas.mias') }}" class="btn btn-ghost">Cancelar</a>
                        <x-primary-button>Pagar {{ '$'.number_format($reserva->sub_total, 0, ',', '.') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
