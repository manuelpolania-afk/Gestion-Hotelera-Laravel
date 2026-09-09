<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Panel de Administración
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

                {{-- Zonas --}}
                <div class="flex flex-col justify-between rounded-lg bg-white p-6 shadow">
                    <div>
                        <div class="mb-3 inline-flex rounded-full bg-blue-100 p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Zonas</h3>
                        <p class="mt-1 text-sm text-gray-500">Gestiona las zonas del hotel donde se agrupan las habitaciones.</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.zonas.index') }}"
                           class="inline-flex w-full items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Ver zonas
                        </a>
                    </div>
                </div>

                {{-- Habitaciones --}}
                <div class="flex flex-col justify-between rounded-lg bg-white p-6 shadow">
                    <div>
                        <div class="mb-3 inline-flex rounded-full bg-indigo-100 p-3">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Habitaciones</h3>
                        <p class="mt-1 text-sm text-gray-500">Administra las habitaciones, sus imágenes, capacidad y estado.</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.habitaciones.index') }}"
                           class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Ver habitaciones
                        </a>
                    </div>
                </div>

                {{-- Reservas (módulo pendiente) --}}
                <div class="flex flex-col justify-between rounded-lg bg-white p-6 shadow">
                    <div>
                        <div class="mb-3 inline-flex rounded-full bg-green-100 p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Reservas</h3>
                        <p class="mt-1 text-sm text-gray-500">Consulta y gestiona las reservas activas y el historial de clientes.</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.reservas.index') }}"
                           class="inline-flex w-full items-center justify-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            Ver reservas
                        </a>
                    </div>
                </div>

                {{-- Usuarios (módulo pendiente) --}}
                <div class="flex flex-col justify-between rounded-lg bg-white p-6 shadow">
                    <div>
                        <div class="mb-3 inline-flex rounded-full bg-purple-100 p-3">
                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Usuarios</h3>
                        <p class="mt-1 text-sm text-gray-500">Administra los usuarios registrados y sus roles en el sistema.</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.habitaciones.index') }}"
                           class="inline-flex w-full items-center justify-center rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                            Ver usuarios
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
