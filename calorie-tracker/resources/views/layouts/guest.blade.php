<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Mealo') }}</title>

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
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">

            {{-- Brand panel — hidden on mobile --}}
            <div class="hidden lg:flex lg:w-5/12 bg-zinc-900 dark:bg-zinc-950 flex-col justify-between p-12 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-900/20 via-transparent to-transparent pointer-events-none"></div>
                <div class="absolute -bottom-32 -right-20 w-64 h-64 rounded-full bg-emerald-500/5 blur-3xl pointer-events-none"></div>

                <div class="relative">
                    <a href="{{ route('landing') }}" class="text-2xl hover:opacity-80 transition">
                        <x-wordmark :dark="true" />
                    </a>
                </div>

                <div class="relative">
                    <p class="font-serif text-4xl text-white leading-snug mb-4">
                        {{ __('Track what you eat.') }}<br>
                        {{ __('Understand your body.') }}
                    </p>
                    <p class="text-zinc-400 text-sm leading-relaxed max-w-xs">
                        {{ __('Log meals in seconds with AI-powered calorie and macro estimation. No manual lookups. No guesswork.') }}
                    </p>
                </div>

                <p class="relative text-xs text-zinc-600">&copy; {{ date('Y') }} Mealo</p>
            </div>

            {{-- Form panel --}}
            <div class="flex-1 flex items-center justify-center px-6 py-12 bg-white dark:bg-zinc-900 relative">

                {{-- Language + theme switchers --}}
                <div class="absolute top-5 end-6 flex items-center gap-1">
                    <form method="POST" action="{{ route('locale.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                        <button type="submit"
                                class="px-2.5 py-1 rounded-md text-xs font-semibold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition tracking-wide">
                            {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                        </button>
                    </form>
                    <button type="button" onclick="toggleTheme()"
                            class="p-1.5 rounded-lg text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                        <svg class="h-4 w-4 hidden dark:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14A7 7 0 0012 5z"/>
                        </svg>
                        <svg class="h-4 w-4 block dark:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                </div>
                <script>
                    function toggleTheme() {
                        var dark = document.documentElement.classList.toggle('dark');
                        localStorage.setItem('theme', dark ? 'dark' : 'light');
                    }
                </script>

                <div class="w-full max-w-sm">
                    <div class="lg:hidden mb-8">
                        <a href="{{ route('landing') }}" class="text-2xl hover:opacity-80 transition">
                            <x-wordmark />
                        </a>
                    </div>
                    {{ $slot }}
                </div>
            </div>

        </div>
    </body>
</html>
