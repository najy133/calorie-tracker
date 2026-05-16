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
