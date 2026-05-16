<x-guest-layout>
    <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-50 mb-1">Create your account</h2>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-8">Start tracking your nutrition today</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1.5 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1.5 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <x-primary-button class="w-full justify-center py-2.5">
            {{ __('Create account') }}
        </x-primary-button>

        <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
            Already have an account?
            <a href="{{ route('login') }}" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline">
                Log in
            </a>
        </p>
    </form>
</x-guest-layout>
