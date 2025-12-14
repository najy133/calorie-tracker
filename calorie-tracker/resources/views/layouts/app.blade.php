<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Calorie Tracker') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-gradient-to-b from-slate-900 via-slate-950 to-black text-slate-50 antialiased">
        <div class="mx-auto flex min-h-screen max-w-5xl flex-col px-4 py-8 sm:px-6 lg:px-12">
            <header class="mb-10 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 text-slate-900 shadow-lg shadow-emerald-500/20">
                        <span class="text-lg font-black">CT</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-200/90">Calorie Tracker</p>
                        <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">Track, plan, and fuel your day</h1>
                        <p class="text-sm text-slate-300">A minimal Livewire + Tailwind starter to extend however you like.</p>
                    </div>
                </div>
                <div class="hidden items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-emerald-100 shadow-sm sm:flex">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span>Live preview</span>
                </div>
            </header>

            <main class="flex-1">
                <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-xl ring-1 ring-white/10 backdrop-blur">
                    {{ $slot }}
                </div>
            </main>

            <footer class="mt-10 text-center text-xs text-slate-500">
                Designed for clarity. Make it yours when you're ready.
            </footer>
        </div>

        @livewireScripts
    </body>
</html>
