@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg bg-stone-900 px-3 py-2.5 text-start text-sm font-medium text-white'
            : 'block w-full rounded-lg px-3 py-2.5 text-start text-sm font-medium text-stone-700 hover:bg-stone-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
