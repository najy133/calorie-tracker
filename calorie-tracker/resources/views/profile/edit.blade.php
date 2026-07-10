<x-app-layout>
    <div class="mx-auto max-w-2xl px-6 md:px-10 py-10 space-y-10">

        @php
            $user        = auth()->user();
            $initial     = mb_strtoupper(mb_substr($user->name ?? '?', 0, 1));
            $memberSince = $user->created_at?->format('F Y');
            $streak      = $streak ?? 0;
        @endphp

        {{-- Header — mirrors the serif greeting used on the dashboard and log pages --}}
        <header class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-emerald-600 text-white font-serif text-2xl flex items-center justify-center shrink-0">
                {{ $initial }}
            </div>
            <div class="min-w-0">
                <h1 class="font-serif text-3xl md:text-4xl text-zinc-900 dark:text-zinc-50 leading-tight truncate">{{ $user->name }}</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 truncate">{{ $user->email }}</p>
                <p class="font-mono text-[11px] uppercase tracking-wider rtl:tracking-normal text-zinc-400 dark:text-zinc-500 mt-1">
                    @if($memberSince){{ __('Member since') }} {{ $memberSince }}@endif
                    @if($streak > 0) <span class="text-emerald-600 dark:text-emerald-400">· {{ __(':count-day streak', ['count' => $streak]) }}</span>@endif
                </p>
            </div>
        </header>

        <section class="border-t border-zinc-100 dark:border-zinc-800/70 pt-8">
            <h2 class="font-serif text-2xl md:text-[1.75rem] text-zinc-900 dark:text-zinc-50 tracking-tight leading-tight mb-1.5">{{ __('Account') }}</h2>
            @include('profile.partials.update-profile-information-form')
        </section>

        <section class="border-t border-zinc-100 dark:border-zinc-800/70 pt-8">
            <h2 class="font-serif text-2xl md:text-[1.75rem] text-zinc-900 dark:text-zinc-50 tracking-tight leading-tight mb-1.5">{{ __('Security') }}</h2>
            @include('profile.partials.update-password-form')
        </section>

        <section class="border-t border-zinc-100 dark:border-zinc-800/70 pt-8">
            <h2 class="font-serif text-2xl md:text-[1.75rem] text-rose-600 dark:text-rose-400 tracking-tight leading-tight mb-1.5">{{ __('Danger zone') }}</h2>
            @include('profile.partials.delete-user-form')
        </section>

    </div>
</x-app-layout>
