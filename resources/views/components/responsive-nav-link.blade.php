@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg px-3 py-2 text-start text-base font-semibold text-white bg-white/10 transition'
            : 'block w-full rounded-lg px-3 py-2 text-start text-base font-medium text-brand-200 hover:bg-white/10 hover:text-white transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
