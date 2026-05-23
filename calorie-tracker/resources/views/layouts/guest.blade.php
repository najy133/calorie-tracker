<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Calorie Tracker') }}</title>

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
                    <a href="{{ route('home') }}" class="text-lg font-semibold text-white tracking-tight">
                        {{ __('Calorie Tracker') }}
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

                <p class="relative text-xs text-zinc-600">&copy; {{ date('Y') }} Calorie Tracker</p>
            </div>

            {{-- Form panel --}}
            <div class="flex-1 flex items-center justify-center px-6 py-12 bg-white dark:bg-zinc-900">
                <div class="w-full max-w-sm">
                    <div class="lg:hidden mb-8">
                        <a href="{{ route('home') }}" class="text-xl font-semibold text-zinc-900 dark:text-zinc-50 tracking-tight">
                            {{ __('Calorie Tracker') }}
                        </a>
                    </div>
                    {{ $slot }}
                </div>
            </div>

        </div>
    </body>
</html>
