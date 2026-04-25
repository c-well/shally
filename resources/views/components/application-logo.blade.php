@props(['compact' => false])
@php($isCompact = $compact || (isset($attributes) && str_contains((string) $attributes->get('class'), 'compact')))
<span {{ $attributes->class(['inline-flex flex-col items-center leading-none select-none']) }}>
    <span style="font-family: 'Cormorant Garamond', ui-serif, Georgia, serif;" class="{{ $isCompact ? 'text-xl' : 'text-4xl' }} font-medium tracking-wide text-gray-800">Church of Peace</span>
    <span class="{{ $isCompact ? 'text-[0.5rem] mt-0.5' : 'text-[0.65rem] mt-1' }} uppercase tracking-[0.35em] text-gray-500">Shalom</span>
</span>
