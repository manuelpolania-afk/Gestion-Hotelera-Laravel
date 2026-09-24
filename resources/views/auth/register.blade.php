<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl font-bold text-brand-900">Crear cuenta</h2>
        <p class="mt-1 text-sm text-brand-400">Regístrate para reservar habitaciones en línea.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input id="name" class="mt-1" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="documento" :value="__('Documento')" />
                <x-text-input id="documento" class="mt-1" type="text" name="documento" :value="old('documento')" required autocomplete="off" />
                <x-input-error :messages="$errors->get('documento')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="telefono" :value="__('Teléfono')" />
                <x-text-input id="telefono" class="mt-1" type="text" name="telefono" :value="old('telefono')" required autocomplete="tel" />
                <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="password" :value="__('Contraseña')" />
                <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
                <x-text-input id="password_confirmation" class="mt-1" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <x-primary-button class="btn-block">
            {{ __('Registrarme') }}
        </x-primary-button>

        <p class="text-center text-sm text-brand-400">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="link">Inicia sesión</a>
        </p>
    </form>
</x-guest-layout>
