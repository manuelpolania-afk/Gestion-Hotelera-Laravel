<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl font-bold text-brand-900">Confirma tu contraseña</h2>
        <p class="mt-1 text-sm text-brand-400">
            Esta es un área segura. Confirma tu contraseña para continuar.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="btn-block">
            {{ __('Confirmar') }}
        </x-primary-button>
    </form>
</x-guest-layout>
