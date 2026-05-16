<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Calorie Tracker') }} — Log a meal before you forget it</title>

    <script>
        (function(){
            var t=localStorage.getItem('theme');
            if(t==='dark'||(!t&&window.matchMedia('(prefers-color-scheme:dark)').matches)){
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600&family=instrument-serif:400&family=dm-mono:400,500&display=swap" rel="stylesheet" />

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
<body class="font-sans antialiased bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 transition-colors duration-200">

    @include('layouts.navigation')

    {{-- ─────────────────────────────────────────────
         HERO  —  left-aligned, 2-column
    ───────────────────────────────────────────── --}}
    <section class="max-w-5xl mx-auto px-6 md:px-10 pt-20 pb-16 md:pt-28 md:pb-24">
        <div class="flex flex-col md:flex-row md:items-center gap-16">

            {{-- Left: copy --}}
            <div class="flex-1">
                <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-6">
                    AI-powered nutrition tracking
                </p>

                <h1 class="font-serif text-5xl md:text-6xl text-zinc-900 dark:text-zinc-50 leading-[1.08] mb-6">
                    Log a meal<br>before you<br>forget it.
                </h1>

                <p class="text-zinc-500 dark:text-zinc-400 text-base leading-relaxed mb-10 max-w-sm">
                    Type what you ate in plain English. No barcode scanning, no database — just your calories and macros back in seconds.
                </p>

                <div class="flex items-center gap-6">
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors duration-150 shadow-sm shadow-emerald-600/20">
                        Get started free
                    </a>
                    <a href="{{ route('home') }}"
                       class="text-sm text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors underline underline-offset-4 decoration-zinc-200 dark:decoration-zinc-700">
                        Try it first
                    </a>
                </div>
            </div>

            {{-- Right: app mockup --}}
            <div class="flex-1 flex justify-center md:justify-end">
                <div class="w-full max-w-xs bg-zinc-900 rounded-2xl p-5 shadow-2xl shadow-zinc-900/20 dark:shadow-black/40 ring-1 ring-white/5">

                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <p class="text-zinc-50 text-sm font-medium">Today</p>
                            <p class="text-zinc-500 text-xs">{{ now()->format('l, M j') }}</p>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-zinc-800 text-zinc-400">Goal: 2,000</span>
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
                            <p class="text-zinc-500 text-xs mt-1">716 kcal remaining</p>
                        </div>
                    </div>

                    {{-- Meal entries --}}
                    <div class="space-y-0 border-t border-zinc-800">
                        <div class="entry-1 flex items-center justify-between py-2.5 border-b border-zinc-800/60">
                            <div class="min-w-0 mr-3">
                                <p class="text-zinc-100 text-xs font-medium truncate">Grilled chicken & rice</p>
                                <p class="text-zinc-600 text-xs mt-0.5">
                                    12:34 PM ·
                                    <span class="text-indigo-400">P52g</span>
                                    <span class="text-amber-400 ml-1">C68g</span>
                                    <span class="text-rose-400 ml-1">F12g</span>
                                </p>
                            </div>
                            <span class="font-mono text-xs text-zinc-300 shrink-0">580 kcal</span>
                        </div>
                        <div class="entry-2 flex items-center justify-between py-2.5 border-b border-zinc-800/60">
                            <div class="min-w-0 mr-3">
                                <p class="text-zinc-100 text-xs font-medium truncate">2 eggs, toast with butter</p>
                                <p class="text-zinc-600 text-xs mt-0.5">
                                    8:15 AM ·
                                    <span class="text-indigo-400">P18g</span>
                                    <span class="text-amber-400 ml-1">C28g</span>
                                    <span class="text-rose-400 ml-1">F22g</span>
                                </p>
                            </div>
                            <span class="font-mono text-xs text-zinc-300 shrink-0">380 kcal</span>
                        </div>
                        <div class="entry-3 flex items-center justify-between py-2.5">
                            <div class="min-w-0 mr-3">
                                <p class="text-zinc-100 text-xs font-medium truncate">Greek yogurt, granola</p>
                                <p class="text-zinc-600 text-xs mt-0.5">
                                    7:30 AM ·
                                    <span class="text-indigo-400">P15g</span>
                                    <span class="text-amber-400 ml-1">C38g</span>
                                    <span class="text-rose-400 ml-1">F6g</span>
                                </p>
                            </div>
                            <span class="font-mono text-xs text-zinc-300 shrink-0">324 kcal</span>
                        </div>
                    </div>

                    {{-- Input --}}
                    <div class="mt-4 rounded-xl bg-zinc-800 border border-zinc-700 px-3 py-2.5">
                        <p class="text-zinc-600 text-xs">Describe what you ate...</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ─────────────────────────────────────────────
         STATEMENT — breaks the rhythm
    ───────────────────────────────────────────── --}}
    <div class="border-y border-zinc-100 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900">
        <div class="max-w-5xl mx-auto px-6 md:px-10 py-12">
            <p class="text-xl md:text-2xl text-zinc-500 dark:text-zinc-400 leading-relaxed max-w-2xl">
                Other apps make you scan a barcode or dig through a database.
                This one just asks what you ate —
                <span class="text-zinc-900 dark:text-zinc-100 font-medium">"chicken salad, medium bowl"</span>
                is enough.
            </p>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────
         INTERACTIVE BURGER
    ───────────────────────────────────────────── --}}
    <section class="bg-zinc-950 py-24">
        <div class="max-w-5xl mx-auto px-6 md:px-10">

            <div class="text-center mb-16">
                <h2 class="font-serif text-4xl text-white mb-3">Break it down</h2>
                <p class="text-zinc-500 text-sm">Hover any layer — the rest gets out of the way</p>
            </div>

            <div x-data="{
                    active: null,
                    order: ['bun-top','bacon','lettuce-top','tomato','cheese','patty','bun-bottom'],
                    above(l){ return this.active && this.order.indexOf(l) < this.order.indexOf(this.active) },
                    below(l){ return this.active && this.order.indexOf(l) > this.order.indexOf(this.active) }
                 }"
                 @mouseleave="active = null"
                 class="flex flex-col md:flex-row items-center justify-center gap-12 md:gap-20">

                {{-- Burger layers --}}
                <div class="flex flex-col items-center gap-[3px] select-none">

                    {{-- Top bun --}}
                    <div @mouseenter="active='bun-top'" @click="active = active==='bun-top' ? null : 'bun-top'"
                         :class="{
                             '-translate-y-16 opacity-0 pointer-events-none': above('bun-top'),
                             'translate-y-16 opacity-0 pointer-events-none': below('bun-top'),
                             'scale-105 -translate-y-2 brightness-110': active==='bun-top'
                         }"
                         class="relative w-48 h-14 cursor-pointer transition-all duration-300 ease-out"
                         title="Top bun">
                        <div class="w-full h-full bg-amber-400 rounded-t-[100%] rounded-b-md shadow-md"></div>
                        {{-- sesame seeds --}}
                        <span class="absolute top-3 left-10 w-2 h-1 bg-amber-200 rounded-full rotate-12 opacity-80"></span>
                        <span class="absolute top-2 left-[4.5rem] w-2 h-1 bg-amber-200 rounded-full -rotate-6 opacity-80"></span>
                        <span class="absolute top-3 right-10 w-2 h-1 bg-amber-200 rounded-full rotate-6 opacity-80"></span>
                        <span class="absolute top-4 right-[4.5rem] w-2 h-1 bg-amber-200 rounded-full -rotate-12 opacity-80"></span>
                        <span class="absolute top-2 left-1/2 -translate-x-1/2 w-2 h-1 bg-amber-200 rounded-full opacity-80"></span>
                    </div>

                    {{-- Bacon --}}
                    <div @mouseenter="active='bacon'" @click="active = active==='bacon' ? null : 'bacon'"
                         :class="{
                             '-translate-y-16 opacity-0 pointer-events-none': above('bacon'),
                             'translate-y-16 opacity-0 pointer-events-none': below('bacon'),
                             'scale-105 -translate-y-1 brightness-110': active==='bacon'
                         }"
                         class="w-44 cursor-pointer transition-all duration-300 ease-out"
                         title="Bacon">
                        <div class="w-full h-3 bg-orange-800 rounded-sm"
                             style="clip-path: polygon(0% 0%, 8% 100%, 16% 20%, 24% 100%, 32% 0%, 40% 100%, 48% 20%, 56% 100%, 64% 0%, 72% 100%, 80% 20%, 88% 100%, 100% 0%, 100% 100%, 0% 100%)"></div>
                    </div>

                    {{-- Lettuce --}}
                    <div @mouseenter="active='lettuce-top'" @click="active = active==='lettuce-top' ? null : 'lettuce-top'"
                         :class="{
                             '-translate-y-16 opacity-0 pointer-events-none': above('lettuce-top'),
                             'translate-y-16 opacity-0 pointer-events-none': below('lettuce-top'),
                             'scale-105 brightness-110': active==='lettuce-top'
                         }"
                         class="w-56 cursor-pointer transition-all duration-300 ease-out"
                         title="Lettuce">
                        <div class="w-full h-5 bg-green-500 rounded-sm"
                             style="clip-path: polygon(0% 60%, 3% 0%, 7% 80%, 11% 10%, 15% 70%, 19% 0%, 23% 75%, 27% 5%, 31% 65%, 35% 0%, 39% 80%, 43% 10%, 47% 70%, 51% 0%, 55% 75%, 59% 5%, 63% 65%, 67% 0%, 71% 80%, 75% 10%, 79% 70%, 83% 0%, 87% 75%, 91% 5%, 95% 65%, 100% 20%, 100% 100%, 0% 100%)"></div>
                    </div>

                    {{-- Tomato --}}
                    <div @mouseenter="active='tomato'" @click="active = active==='tomato' ? null : 'tomato'"
                         :class="{
                             '-translate-y-16 opacity-0 pointer-events-none': above('tomato'),
                             'translate-y-16 opacity-0 pointer-events-none': below('tomato'),
                             'scale-105 brightness-110': active==='tomato'
                         }"
                         class="w-44 cursor-pointer transition-all duration-300 ease-out"
                         title="Tomato">
                        <div class="w-full h-4 bg-red-500 rounded-full shadow-sm"></div>
                    </div>

                    {{-- Cheese --}}
                    <div @mouseenter="active='cheese'" @click="active = active==='cheese' ? null : 'cheese'"
                         :class="{
                             '-translate-y-16 opacity-0 pointer-events-none': above('cheese'),
                             'translate-y-16 opacity-0 pointer-events-none': below('cheese'),
                             'scale-105 brightness-110': active==='cheese'
                         }"
                         class="w-48 cursor-pointer transition-all duration-300 ease-out"
                         title="Cheese">
                        <div class="w-full h-2.5 bg-yellow-400 rounded-sm shadow-sm"></div>
                    </div>

                    {{-- Patty --}}
                    <div @mouseenter="active='patty'" @click="active = active==='patty' ? null : 'patty'"
                         :class="{
                             '-translate-y-16 opacity-0 pointer-events-none': above('patty'),
                             'translate-y-16 opacity-0 pointer-events-none': below('patty'),
                             'scale-105 translate-y-1 brightness-110': active==='patty'
                         }"
                         class="w-44 cursor-pointer transition-all duration-300 ease-out"
                         title="Beef patty">
                        <div class="w-full h-8 bg-stone-800 rounded-md shadow-md"></div>
                    </div>

                    {{-- Bottom bun --}}
                    <div @mouseenter="active='bun-bottom'" @click="active = active==='bun-bottom' ? null : 'bun-bottom'"
                         :class="{
                             '-translate-y-16 opacity-0 pointer-events-none': above('bun-bottom'),
                             'translate-y-16 opacity-0 pointer-events-none': below('bun-bottom'),
                             'scale-105 translate-y-2 brightness-110': active==='bun-bottom'
                         }"
                         class="w-48 cursor-pointer transition-all duration-300 ease-out"
                         title="Bottom bun">
                        <div class="w-full h-7 bg-amber-300 rounded-b-[60%] rounded-t-sm shadow-md"></div>
                    </div>

                </div>

                {{-- Macro info panel --}}
                <div class="w-52 min-h-40 flex items-center">

                    <div x-show="!active" class="text-zinc-600 text-sm text-center w-full">
                        <p>← hover a layer</p>
                    </div>

                    @foreach([
                        'bun-top'     => ['name' => 'Top Bun',     'color' => 'bg-amber-400',  'cal' => 130, 'protein' => '4g',   'carbs' => '25g', 'fat' => '2g'],
                        'bacon'       => ['name' => 'Bacon',       'color' => 'bg-orange-700', 'cal' => 90,  'protein' => '6g',   'carbs' => '0g',  'fat' => '7g'],
                        'lettuce-top' => ['name' => 'Lettuce',     'color' => 'bg-green-500',  'cal' => 5,   'protein' => '0.5g', 'carbs' => '1g',  'fat' => '0g'],
                        'tomato'      => ['name' => 'Tomato',      'color' => 'bg-red-500',    'cal' => 15,  'protein' => '0.7g', 'carbs' => '3g',  'fat' => '0g'],
                        'cheese'      => ['name' => 'Cheddar',     'color' => 'bg-yellow-400', 'cal' => 70,  'protein' => '4g',   'carbs' => '0.5g','fat' => '6g'],
                        'patty'       => ['name' => 'Beef Patty',  'color' => 'bg-stone-700',  'cal' => 250, 'protein' => '22g',  'carbs' => '0g',  'fat' => '17g'],
                        'bun-bottom'  => ['name' => 'Bottom Bun',  'color' => 'bg-amber-300',  'cal' => 115, 'protein' => '3g',   'carbs' => '22g', 'fat' => '2g'],
                    ] as $key => $layer)
                        <div x-show="active === '{{ $key }}'" x-cloak class="w-full">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-2.5 h-2.5 rounded-full {{ $layer['color'] }}"></span>
                                <p class="text-white font-semibold text-sm">{{ $layer['name'] }}</p>
                            </div>
                            <div class="space-y-2.5">
                                <div class="flex justify-between">
                                    <span class="text-zinc-500 text-xs">Calories</span>
                                    <span class="font-mono text-white text-xs font-medium">{{ $layer['cal'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-indigo-400 text-xs">Protein</span>
                                    <span class="font-mono text-white text-xs font-medium">{{ $layer['protein'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-amber-400 text-xs">Carbs</span>
                                    <span class="font-mono text-white text-xs font-medium">{{ $layer['carbs'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-rose-400 text-xs">Fat</span>
                                    <span class="font-mono text-white text-xs font-medium">{{ $layer['fat'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

            <p class="text-center text-xs text-zinc-700 mt-14">Full burger: ~675 kcal · P 40g · C 51g · F 34g</p>

        </div>
    </section>

    {{-- ─────────────────────────────────────────────
         WHAT YOU GET  —  prose, not cards
    ───────────────────────────────────────────── --}}
    <section class="max-w-5xl mx-auto px-6 md:px-10 py-20">
        <div class="grid md:grid-cols-2 gap-x-16 gap-y-10">

            <div>
                <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest mb-3">Calories aren't the whole story</p>
                <p class="text-zinc-700 dark:text-zinc-300 text-sm leading-relaxed">
                    Every estimate comes with protein, carbs, and fat alongside calories. Colour-coded in your history so you can read a day at a glance without doing the maths yourself.
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest mb-3">One look is enough</p>
                <p class="text-zinc-700 dark:text-zinc-300 text-sm leading-relaxed">
                    Your progress ring fills as you log. It shifts from green to amber as you near your goal, and red if you go over — no configuration required.
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest mb-3">Find the pattern</p>
                <p class="text-zinc-700 dark:text-zinc-300 text-sm leading-relaxed">
                    A bar chart of your last seven days so you can spot patterns — not just how today went, but whether Tuesday is always the problem.
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest mb-3">The chain effect</p>
                <p class="text-zinc-700 dark:text-zinc-300 text-sm leading-relaxed">
                    Log at least once a day and you build a streak. Simple, but it works — the same reason you don't want to break a chain once it gets long enough.
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
                    Start logging.<br>It takes a minute.
                </h2>
                <p class="text-zinc-400 dark:text-zinc-500 text-sm">No credit card. No onboarding flow. Just log a meal.</p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center px-6 py-3 rounded-lg bg-zinc-900 dark:bg-zinc-50 text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-200 transition-colors duration-150">
                    Create free account →
                </a>
                <p class="mt-3 text-xs text-zinc-400 dark:text-zinc-600">
                    Already have one?
                    <a href="{{ route('login') }}" class="hover:text-zinc-600 dark:hover:text-zinc-400 transition underline underline-offset-2">Log in</a>
                </p>
            </div>
        </div>
    </section>

    <footer class="border-t border-zinc-100 dark:border-zinc-800 py-6">
        <div class="max-w-5xl mx-auto px-6 md:px-10 flex items-center justify-between text-xs text-zinc-400 dark:text-zinc-600">
            <span>Calorie Tracker</span>
            <span>&copy; {{ date('Y') }}</span>
        </div>
    </footer>

</body>
</html>
