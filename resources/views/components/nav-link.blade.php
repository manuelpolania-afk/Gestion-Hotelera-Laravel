@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex min-h-[44px] items-center rounded-full bg-brand-950 px-4 text-[0.9375rem] text-white transition'
            : 'inline-flex min-h-[44px] items-center rounded-full px-4 text-[0.9375rem] text-brand-700 transition hover:bg-brand-100 hover:text-brand-950';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
