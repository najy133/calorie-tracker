@props(['dark' => false])

{{--
    Mealo wordmark — "Meal" + the emerald progress ring as the "o".
    Implemented per the design spec (MEALO-LOGO-NOTES): DM Sans 600, ring at
    0.72em with a 0.16em top offset to align to the x-height, 64% arc, rotated
    -90° so the arc starts at 12 o'clock. dir="ltr" keeps it stable in RTL.
    Pass :dark="true" for the always-dark auth panel.
--}}
<span dir="ltr" {{ $attributes->merge(['class' => 'inline-flex items-center font-semibold tracking-tight ' . ($dark ? 'text-white' : 'text-zinc-900 dark:text-zinc-50')]) }}>Meal<svg class="ml-[0.03em] mt-[0.28em]" style="width:0.72em;height:0.72em" viewBox="0 0 48 48" fill="none" aria-hidden="true">
        <circle cx="24" cy="24" r="19" stroke-width="7"
                class="{{ $dark ? 'stroke-zinc-700' : 'stroke-emerald-100 dark:stroke-zinc-800' }}" />
        <circle cx="24" cy="24" r="19" stroke-width="7"
                stroke-dasharray="119.4" stroke-dashoffset="43" stroke-linecap="round"
                transform="rotate(-90 24 24)"
                class="{{ $dark ? 'stroke-emerald-500' : 'stroke-emerald-600 dark:stroke-emerald-500' }}" />
    </svg><span class="sr-only">o — Mealo</span></span>
