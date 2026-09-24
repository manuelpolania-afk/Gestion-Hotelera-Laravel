<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl font-bold text-brand-900">Recuperar contraseña</h2>
        <p class="mt-1 text-sm text-brand-400">
            Te enviaremos un enlace para restablecer tu contraseña.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="btn-block">
            {{ __('Enviar enlace') }}
        </x-primary-button>
    </form>
</x-guest-layout>
