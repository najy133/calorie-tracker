<x-guest-layout>
    <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-50 mb-1">{{ __('Welcome back') }}</h2>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-8">{{ __('Log in to track your calories') }}</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox"
                       class="rounded border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 text-emerald-600 shadow-sm focus:ring-emerald-500"
                       name="remember">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition"
                   href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="w-full justify-center py-2.5">
            {{ __('Log in') }}
        </x-primary-button>

        <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('Don\'t have an account?') }}
            <a href="{{ route('register') }}" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline">
                {{ __('Sign up free') }}
            </a>
        </p>
    </form>
</x-guest-layout>
