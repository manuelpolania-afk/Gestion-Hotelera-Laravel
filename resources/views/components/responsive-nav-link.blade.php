@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-[14px] bg-brand-950 px-4 py-3 text-start text-lg text-white transition'
            : 'block w-full rounded-[14px] px-4 py-3 text-start text-lg text-brand-800 transition hover:bg-brand-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
