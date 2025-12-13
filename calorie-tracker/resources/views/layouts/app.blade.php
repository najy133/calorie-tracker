<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Calorie Tracker') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-gray-100 text-gray-900">
        <div class="flex min-h-screen items-center justify-center p-6">
            <div class="w-full max-w-5xl">
                <header class="mb-8 text-center">
                    <h1 class="text-3xl font-bold text-gray-900">Calorie Tracker</h1>
                    <p class="mt-2 text-gray-600">A minimal Livewire + Tailwind starter</p>
                </header>

                <main>
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
