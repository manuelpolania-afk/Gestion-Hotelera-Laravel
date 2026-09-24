<x-app-layout title="Mi perfil">
    <x-slot name="header">
        <h2 class="page-title">Mi Perfil</h2>
    </x-slot>

    <div class="container-page py-8">
        <div class="mx-auto max-w-2xl space-y-6">
            <div class="card card-pad sm:p-8">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="card card-pad sm:p-8">
                @include('profile.partials.update-password-form')
            </div>

            <div class="card card-pad sm:p-8">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
