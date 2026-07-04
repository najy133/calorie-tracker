<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <title>{{ config('app.name', 'Mealo') }} — {{ __('Log a meal before you forget it.') }}</title>

    <script>
        (function(){
            var t=localStorage.getItem('theme');
            if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme:dark)').matches)){
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600&family=instrument-serif:400&family=dm-mono:400,500&family=cairo:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        @keyframes ring-draw {
            from { stroke-dashoffset: 226; }
            to   { stroke-dashoffset: 68; }
        }
        .ring-progress {
            animation: ring-draw 1.4s cubic-bezier(0.4, 0, 0.2, 1) 0.3s both;
        }
        @keyframes entry-in {
            from { opacity: 0; transform: translateX(12px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .entry-1 { animation: entry-in 0.4s ease both 1s; }
        .entry-2 { animation: entry-in 0.4s ease both 1.15s; }
        .entry-3 { animation: entry-in 0.4s ease both 1.3s; }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-b from-green-50 via-zinc-50 to-white dark:from-zinc-950 dark:via-zinc-950 dark:to-zinc-950 text-zinc-900 dark:text-zinc-50 transition-colors duration-200">

    @include('layouts.navigation')

    {{-- ─────────────────────────────────────────────
         HERO  —  left-aligned, 2-column
    ───────────────────────────────────────────── --}}
    <section class="max-w-5xl mx-auto px-6 md:px-10 pt-20 pb-16 md:pt-28 md:pb-24">
        <div class="flex flex-col md:flex-row md:items-center gap-16">

            {{-- Left: copy --}}
            <div class="flex-1">
                <p class="text-xs rtl:text-sm font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest rtl:tracking-normal mb-6">
                    {{ __('AI-powered nutrition tracking') }}
                </p>

                <h1 class="font-serif text-5xl md:text-6xl text-zinc-900 dark:text-zinc-50 leading-[1.08] mb-6">
                    {{ __('Log a meal before you forget it.') }}
                </h1>

                <p class="text-zinc-500 dark:text-zinc-400 text-base leading-relaxed mb-10 max-w-sm">
                    {{ __('Type what you ate in plain English. No barcode scanning, no database — just your calories and macros back in seconds.') }}
                </p>

                <div class="flex items-center gap-6">
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors duration-150 shadow-sm shadow-emerald-600/20">
                        {{ __('Get started free') }}
                    </a>
                    <a href="{{ route('home') }}"
                       class="text-sm text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors underline underline-offset-4 decoration-zinc-200 dark:decoration-zinc-700">
                        {{ __('Try it first') }}
                    </a>
                </div>
            </div>

            {{-- Right: app mockup --}}
            <div class="flex-1 flex justify-center md:justify-end">
                <div class="w-full max-w-xs bg-zinc-900 rounded-2xl p-5 shadow-2xl shadow-zinc-900/20 dark:shadow-black/40 ring-1 ring-white/5">

                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <p class="text-zinc-50 text-sm font-medium">{{ __('Today') }}</p>
                            <p class="text-zinc-500 text-xs">{{ now()->locale(app()->getLocale())->translatedFormat(app()->getLocale() === 'ar' ? 'l، j M' : 'l, M j') }}</p>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-zinc-800 text-zinc-400">{{ __('Goal: :value kcal', ['value' => '2,000']) }}</span>
                    </div>

                    {{-- Calorie ring + number --}}
                    <div class="flex items-center gap-5 mb-5">
                        <div class="relative w-16 h-16 shrink-0">
                            <svg class="-rotate-90 w-full h-full" viewBox="0 0 72 72">
                                <circle cx="36" cy="36" r="30" fill="none" stroke="#27272a" stroke-width="7"/>
                                <circle cx="36" cy="36" r="30" fill="none" stroke="#10b981" stroke-width="7"
                                        stroke-dasharray="188.5"
                                        stroke-dashoffset="68"
                                        stroke-linecap="round"
                                        class="ring-progress"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="font-mono text-xs font-bold text-zinc-50 leading-none">64%</span>
                            </div>
                        </div>
                        <div>
                            <p class="font-mono text-2xl font-bold text-zinc-50 leading-none">1,284</p>
                            <p class="text-zinc-500 text-xs mt-1">{{ __(':value kcal remaining', ['value' => '716']) }}</p>
                        </div>
                    </div>

                    {{-- Meal entries --}}
                    <div class="space-y-0 border-t border-zinc-800">
                        <div class="entry-1 flex items-center justify-between py-2.5 border-b border-zinc-800/60">
                            <div class="min-w-0 me-3">
                                <p class="text-zinc-100 text-xs font-medium truncate">{{ __('Grilled chicken & rice') }}</p>
                                <p class="text-zinc-600 text-xs mt-0.5">
                                    12:34 {{ __('PM') }} ·
                                    <span class="text-indigo-400">{{ __('P') }}52g</span>
                                    <span class="text-amber-400 ms-1">{{ __('C') }}68g</span>
                                    <span class="text-rose-400 ms-1">{{ __('F') }}12g</span>
                                </p>
                            </div>
                            <span class="font-mono text-xs text-zinc-300 shrink-0">580 kcal</span>
                        </div>
                        <div class="entry-2 flex items-center justify-between py-2.5 border-b border-zinc-800/60">
                            <div class="min-w-0 me-3">
                                <p class="text-zinc-100 text-xs font-medium truncate">{{ __('2 eggs, toast with butter') }}</p>
                                <p class="text-zinc-600 text-xs mt-0.5">
                                    8:15 {{ __('AM') }} ·
                                    <span class="text-indigo-400">{{ __('P') }}18g</span>
                                    <span class="text-amber-400 ms-1">{{ __('C') }}28g</span>
                                    <span class="text-rose-400 ms-1">{{ __('F') }}22g</span>
                                </p>
                            </div>
                            <span class="font-mono text-xs text-zinc-300 shrink-0">380 kcal</span>
                        </div>
                        <div class="entry-3 flex items-center justify-between py-2.5">
                            <div class="min-w-0 me-3">
                                <p class="text-zinc-100 text-xs font-medium truncate">{{ __('Greek yogurt, granola') }}</p>
                                <p class="text-zinc-600 text-xs mt-0.5">
                                    7:30 {{ __('AM') }} ·
                                    <span class="text-indigo-400">{{ __('P') }}15g</span>
                                    <span class="text-amber-400 ms-1">{{ __('C') }}38g</span>
                                    <span class="text-rose-400 ms-1">{{ __('F') }}6g</span>
                                </p>
                            </div>
                            <span class="font-mono text-xs text-zinc-300 shrink-0">324 kcal</span>
                        </div>
                    </div>

                    {{-- Input --}}
                    <div class="mt-4 rounded-xl bg-zinc-800 border border-zinc-700 px-3 py-2.5">
                        <p class="text-zinc-600 text-xs">{{ __('Describe what you ate...') }}</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

<x-meal-breakdown/>

    <section class="max-w-5xl mx-auto px-6 md:px-10 py-20">
        <div class="grid md:grid-cols-2 gap-x-16 gap-y-12">

            {{-- Macros are colors --}}
            <div>
                <div class="h-12 mb-4 flex items-center" aria-hidden="true">
                    <svg width="44" height="44" viewBox="0 0 44 44">
                        <rect x="6" y="22" width="32" height="3" rx="1.5" class="fill-zinc-100 dark:fill-zinc-800"/>
                        <rect x="6" y="22" width="20" height="3" rx="1.5" fill="#6366f1"/>
                        <rect x="6" y="28" width="32" height="3" rx="1.5" class="fill-zinc-100 dark:fill-zinc-800"/>
                        <rect x="6" y="28" width="28" height="3" rx="1.5" fill="#f59e0b"/>
                        <rect x="6" y="34" width="32" height="3" rx="1.5" class="fill-zinc-100 dark:fill-zinc-800"/>
                        <rect x="6" y="34" width="10" height="3" rx="1.5" fill="#f43f5e"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest rtl:tracking-normal mb-3">{{ __('Macros, not just calories') }}</p>
                <p class="text-zinc-700 dark:text-zinc-300 text-sm leading-relaxed">
                    {{ __('Protein, carbs, and fat colour-coded next to every meal, so you can read a day at a glance without doing the maths yourself.') }}
                </p>
            </div>

            {{-- Ring states --}}
            <div>
                <div class="h-12 mb-4 flex items-center" aria-hidden="true">
                    <svg width="44" height="44" viewBox="0 0 56 56" style="transform: rotate(-90deg);">
                        <circle cx="28" cy="28" r="22" fill="none" class="stroke-zinc-100 dark:stroke-zinc-800" stroke-width="6"/>
                        <defs>
                            <linearGradient id="wygRingGrad" x1="0" x2="1" y1="0" y2="0">
                                <stop offset="0%"  stop-color="#10b981"/>
                                <stop offset="50%" stop-color="#f59e0b"/>
                                <stop offset="100%" stop-color="#f43f5e"/>
                            </linearGradient>
                        </defs>
                        <circle cx="28" cy="28" r="22" fill="none" stroke="url(#wygRingGrad)" stroke-width="6" stroke-dasharray="138.2" stroke-dashoffset="14" stroke-linecap="round"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest rtl:tracking-normal mb-3">{{ __('One look is enough') }}</p>
                <p class="text-zinc-700 dark:text-zinc-300 text-sm leading-relaxed">
                    {{ __('Your ring shifts from green to amber as you near your goal, and red if you go over — no configuration required.') }}
                </p>
            </div>

            {{-- Weekly bars --}}
            <div>
                <div class="h-12 mb-4 flex items-center" aria-hidden="true">
                    <svg width="56" height="44" viewBox="0 0 70 56">
                        <rect x="2"  y="32" width="6" height="22" rx="2" fill="#34d399"/>
                        <rect x="12" y="22" width="6" height="32" rx="2" fill="#34d399"/>
                        <rect x="22" y="12" width="6" height="42" rx="2" fill="#fbbf24"/>
                        <rect x="32" y="26" width="6" height="28" rx="2" fill="#34d399"/>
                        <rect x="42" y="18" width="6" height="36" rx="2" fill="#fbbf24"/>
                        <rect x="52" y="28" width="6" height="26" rx="2" fill="#34d399"/>
                        <rect x="62" y="36" width="6" height="18" rx="2" fill="#34d399"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest rtl:tracking-normal mb-3">{{ __('Find the pattern') }}</p>
                <p class="text-zinc-700 dark:text-zinc-300 text-sm leading-relaxed">
                    {{ __('Seven days at a glance — not just how today went, but whether Tuesday is always the problem.') }}
                </p>
            </div>

            {{-- Streak flame --}}
            <div>
                <div class="h-12 mb-4 flex items-center" aria-hidden="true">
                    <svg width="44" height="44" viewBox="0 0 56 56" fill="none">
                        <path d="M28 6c2 8 12 10 12 22a12 12 0 1 1-24 0c0-7 4-10 6-15 1 4 4 5 6-7z" fill="#fbbf24" stroke="#f59e0b" stroke-width="1.5"/>
                        <path d="M28 22c1 4 6 5 6 11a6 6 0 1 1-12 0c0-3 2-5 3-7 1 2 2 3 3-4z" fill="#fff" opacity="0.6"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest rtl:tracking-normal mb-3">{{ __('The chain effect') }}</p>
                <p class="text-zinc-700 dark:text-zinc-300 text-sm leading-relaxed">
                    {{ __('Log at least once a day and you build a streak. Simple, but it works — the same reason you don\'t break a chain once it\'s long.') }}
                </p>
            </div>

        </div>
    </section>

    {{-- ─────────────────────────────────────────────
         FINAL CTA  —  no dark box, just space and intent
    ───────────────────────────────────────────── --}}
    <section class="border-t border-zinc-100 dark:border-zinc-800 py-20">
        <div class="max-w-5xl mx-auto px-6 md:px-10 flex flex-col md:flex-row md:items-end md:justify-between gap-8">
            <div>
                <h2 class="font-serif text-4xl md:text-5xl text-zinc-900 dark:text-zinc-50 leading-tight mb-3">
                    {{ __('Start logging.') }}<br>{{ __('It takes a minute.') }}
                </h2>
                <p class="text-zinc-400 dark:text-zinc-500 text-sm">{{ __('No credit card. No onboarding flow. Just log a meal.') }}</p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center px-6 py-3 rounded-lg bg-zinc-900 dark:bg-zinc-50 text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-200 transition-colors duration-150">
                    {{ __('Create free account →') }}
                </a>
                <p class="mt-3 text-xs text-zinc-400 dark:text-zinc-600">
                    {{ __('Already have one?') }}
                    <a href="{{ route('login') }}" class="hover:text-zinc-600 dark:hover:text-zinc-400 transition underline underline-offset-2">{{ __('Log in') }}</a>
                </p>
            </div>
        </div>
    </section>

    <footer class="border-t border-zinc-100 dark:border-zinc-800 py-6">
        <div class="max-w-5xl mx-auto px-6 md:px-10 flex items-center justify-between text-xs text-zinc-400 dark:text-zinc-600">
            <x-wordmark class="text-sm !font-medium text-zinc-400 dark:text-zinc-600" />
            <span>&copy; {{ date('Y') }}</span>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
