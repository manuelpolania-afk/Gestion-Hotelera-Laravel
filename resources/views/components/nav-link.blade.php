@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold text-white bg-white/10 transition'
            : 'inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-brand-200 hover:text-white hover:bg-white/10 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
