<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-display text-2xl font-bold text-brand-900">Verifica tu email</h2>
        <p class="mt-1 text-sm text-brand-400">
            Te enviamos un enlace de verificación. Si no lo recibiste, podemos enviarte otro.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4">
            {{ __('Se ha enviado un nuevo enlace de verificación a tu correo.') }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                {{ __('Reenviar email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm link">
                {{ __('Cerrar sesión') }}
            </button>
        </form>
    </div>
</x-guest-layout>
