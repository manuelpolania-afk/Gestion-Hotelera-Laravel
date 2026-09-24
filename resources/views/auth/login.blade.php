<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl font-bold text-brand-900">Iniciar sesión</h2>
        <p class="mt-1 text-sm text-brand-400">Accede a tu cuenta para gestionar tus reservas.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-brand-300 text-gold-500 shadow-sm focus:ring-gold-400" name="remember">
                <span class="ms-2 text-sm text-brand-500">{{ __('Recordarme') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm link" href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="btn-block">
            {{ __('Ingresar') }}
        </x-primary-button>

        <p class="text-center text-sm text-brand-400">
            ¿No tienes cuenta?
            <a href="{{ route('register') }}" class="link">Regístrate</a>
        </p>
    </form>
</x-guest-layout>
