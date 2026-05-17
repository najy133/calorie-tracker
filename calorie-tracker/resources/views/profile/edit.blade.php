<x-app-layout>
    <div class="mx-auto max-w-3xl px-6 md:px-10 py-8 space-y-8">

        {{-- ─────────────────────────────────────────────
             Hero — avatar + name + meta
             ───────────────────────────────────────────── --}}
        @php
            $user        = auth()->user();
            $initial     = mb_strtoupper(mb_substr($user->name ?? '?', 0, 1));
            $memberSince = $user->created_at?->format('F Y');
            $streak      = $streak ?? 0;
        @endphp

        <header class="flex items-center gap-4 p-5 rounded-2xl bg-gradient-to-br from-white via-emerald-50/40 to-white dark:from-zinc-900 dark:via-emerald-950/20 dark:to-zinc-900 border border-zinc-200 dark:border-zinc-800">
            <div class="w-14 h-14 rounded-full bg-emerald-600 text-white font-serif text-2xl flex items-center justify-center shrink-0">
                {{ $initial }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-base font-semibold text-zinc-900 dark:text-zinc-50 truncate">{{ $user->name }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 truncate">{{ $user->email }}</p>
                <p class="font-mono text-[11px] uppercase tracking-wider text-zinc-400 dark:text-zinc-600 mt-1">
                    @if($memberSince)Member since {{ $memberSince }}@endif
                    @if($streak > 0) <span class="text-amber-600 dark:text-amber-400">· 🔥 {{ $streak }}-day streak</span>@endif
                </p>
            </div>
        </header>

        {{-- ─────────────────────────────────────────────
             ACCOUNT
             ───────────────────────────────────────────── --}}
        <section>
            <p class="text-[11px] font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-3 px-1">Account</p>
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
                @include('profile.partials.update-profile-information-form')
            </div>
        </section>

        {{-- ─────────────────────────────────────────────
             SECURITY
             ───────────────────────────────────────────── --}}
        <section>
            <p class="text-[11px] font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-3 px-1">Security</p>
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
                @include('profile.partials.update-password-form')
            </div>
        </section>

        {{-- ─────────────────────────────────────────────
             DANGER ZONE — visually demoted: lighter card,
             smaller heading, soft danger button.
             ───────────────────────────────────────────── --}}
        <section>
            <p class="text-[11px] font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-3 px-1">Danger zone</p>
            <div class="rounded-2xl bg-zinc-50/60 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 p-5">
                @include('profile.partials.delete-user-form')
            </div>
        </section>

    </div>
</x-app-layout>
