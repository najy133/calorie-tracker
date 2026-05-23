@props([
    'size'       => 28,
    'stroke'     => 5,
    'progress'   => 1,
    'color'      => 'currentColor',
    'trackColor' => 'none',
    'pulse'      => false,
])
@php
    $r       = ($size - $stroke) / 2;
    $circumf = 2 * M_PI * $r;
    $offset  = $circumf * (1 - $progress);
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}"
     {{ $attributes->class(['ring-spin' => $pulse]) }}>
    @if($trackColor !== 'none')
        <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}"
                fill="none" stroke="{{ $trackColor }}" stroke-width="{{ $stroke }}" />
    @endif
    <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}"
            fill="none"
            stroke="{{ $color }}"
            stroke-width="{{ $stroke }}"
            stroke-dasharray="{{ number_format($circumf, 4) }}"
            stroke-dashoffset="{{ number_format($offset, 4) }}"
            stroke-linecap="round"
            transform="rotate(-90 {{ $size/2 }} {{ $size/2 }})"
            class="transition-all duration-700" />
</svg>
