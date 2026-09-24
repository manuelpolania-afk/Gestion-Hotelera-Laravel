<x-app-layout title="Inicio">
    <x-slot name="header">
        <h2 class="page-title">Hola, {{ Auth::user()->name }}</h2>
    </x-slot>

    <div class="container-page py-8">
        <div class="card card-pad">
            <h3 class="font-display text-lg font-bold text-brand-900">Bienvenido de nuevo</h3>
            <p class="mt-1 text-sm text-brand-500">¿Qué te gustaría hacer hoy?</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('home') }}" class="btn btn-primary">Ver habitaciones</a>
                <a href="{{ route('reservas.mias') }}" class="btn btn-outline">Mis reservas</a>
                <a href="{{ route('profile.edit') }}" class="btn btn-ghost">Editar perfil</a>
            </div>
        </div>
    </div>
</x-app-layout>
