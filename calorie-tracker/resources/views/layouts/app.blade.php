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
        @livewireStyles
    </head>
    <body class="font-sans antialiased min-h-screen bg-gradient-to-b from-green-50 via-zinc-50 to-white dark:from-zinc-950 dark:via-zinc-950 dark:to-zinc-950 text-zinc-900 dark:text-zinc-50 transition-colors duration-200">
        @include('layouts.navigation')

        <main>
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
