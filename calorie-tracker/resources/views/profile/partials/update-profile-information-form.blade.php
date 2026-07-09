<section>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6">
        {{ __('Update your name, email, and daily calorie target.') }}
    </p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail)
                @if ($user->hasVerifiedEmail())
                    <p class="mt-2 text-xs text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ __('Verified') }}
                    </p>
                @else
                    <p class="text-xs mt-2 text-zinc-700 dark:text-zinc-300">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline">
                            {{ __('Resend verification email') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-xs text-emerald-600 dark:text-emerald-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                @endif
            @endif
        </div>

        {{-- Daily goal — presets + custom field --}}
        <div x-data="{ goal: '{{ old('daily_goal', $user->daily_goal ?? 2000) }}', presets: [1500, 2000, 2500, 3000] }">
            <x-input-label for="daily_goal" :value="__('Daily calorie goal')" />

            <div class="mt-1.5 flex items-center gap-2 flex-wrap">
                <template x-for="p in presets" :key="p">
                    <button type="button"
                            @click="goal = p"
                            :class="parseInt(goal) === p
                                ? 'bg-emerald-600 text-white border-emerald-600'
                                : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600'"
                            class="font-mono text-xs px-3 py-1.5 rounded-lg border transition"
                            x-text="p.toLocaleString()">
                    </button>
                </template>

                <div class="relative flex-1 min-w-[140px]">
                    <input id="daily_goal"
                           name="daily_goal"
                           type="number"
                           min="500" max="10000"
                           x-model="goal"
                           required
                           class="w-full pe-12 ps-3 py-1.5 font-mono text-sm border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm transition"/>
                    <span class="absolute end-3 top-1/2 -translate-y-1/2 text-xs text-zinc-400 dark:text-zinc-500 pointer-events-none">kcal</span>
                </div>
            </div>
            <x-input-error class="mt-1.5" :messages="$errors->get('daily_goal')" />
            <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-2">
                {{ __('Typical adult range is 1,800–2,500 depending on activity. The ring will warn at 80% and turn red over 100%.') }}
            </p>
        </div>

        <div class="flex items-center gap-4 pt-1 flex-wrap">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <button type="submit" form="onboarding-reset-form"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-emerald-200 dark:border-emerald-800/60 text-sm font-medium text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:border-emerald-300 dark:hover:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 transition">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5z"/></svg>
                {{ __('Recalculate target with AI') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    {{ __('Saved') }}
                </p>
            @endif
        </div>
    </form>

    {{-- Standalone form referenced by the Recalculate button above --}}
    <form id="onboarding-reset-form" method="POST" action="{{ route('onboarding.reset') }}">
        @csrf
    </form>
</section>
