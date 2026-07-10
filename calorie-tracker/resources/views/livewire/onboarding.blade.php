<div class="min-h-screen bg-gradient-to-b from-emerald-50 via-zinc-50 to-white dark:from-zinc-950 dark:via-zinc-950 dark:to-zinc-950 relative">

    {{-- Ambient glow --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[700px] h-[400px] rounded-full"
             style="background: radial-gradient(ellipse at center, rgb(16 185 129 / 0.08) 0%, transparent 70%);"></div>
    </div>

    {{-- Header --}}
    <header x-data="{
                dark: document.documentElement.classList.contains('dark'),
                toggle() {
                    this.dark = !this.dark;
                    document.documentElement.classList.toggle('dark', this.dark);
                    localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                }
            }"
            class="relative z-10 flex items-center justify-between px-6 md:px-8 py-5">
        <a href="{{ route('dashboard') }}" class="text-2xl hover:opacity-80 transition"><x-wordmark /></a>

        <div class="flex items-center gap-2">
            {{-- Language toggle --}}
            <form method="POST" action="{{ route('locale.switch') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                <button type="submit"
                        class="px-2.5 py-1 rounded-md text-xs font-semibold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition tracking-wide">
                    {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                </button>
            </form>

            {{-- Dark mode toggle --}}
            <button type="button" @click="toggle()"
                    class="p-1.5 rounded-lg text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                    :title="dark ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}'">
                <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14A7 7 0 0012 5z"/>
                </svg>
                <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            @if($step !== 'done')
                <button wire:click="chooseManual" class="text-sm text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 transition ms-1">
                    {{ $step === 'welcome' ? __('Skip for now') : __('Exit setup') }}
                </button>
            @endif
        </div>
    </header>

    {{-- Step navigator + progress bar --}}
    @php
        $stepNum  = match($step) { 'details' => 1, 'activity' => 2, 'goal' => 3, 'eating' => 4, 'context' => 5, 'calc', 'result' => 6, default => 0 };
        $navIds      = array_column($navSteps, 'id');
        $currentIdx  = ($k = array_search($step, $navIds)) !== false ? $k : -1;
    @endphp
    @if(!in_array($step, ['calc', 'done']))
        <div class="relative z-10 px-6 md:px-8 mb-4">
            <div class="max-w-[560px] mx-auto">

                {{-- Stepper: numbered nodes + connectors read as navigable steps
                     (done = check, current = ring, upcoming = muted). Labels show on
                     desktop; mobile shows the current step name below the nodes. --}}
                <div class="flex items-start">
                    @foreach($navSteps as $idx => $s)
                        @php $done = $idx < $currentIdx; $current = $idx === $currentIdx; @endphp
                        <div class="flex-1 flex flex-col items-center">
                            <div class="flex items-center w-full">
                                {{-- left connector --}}
                                <div class="h-0.5 flex-1 rounded-full {{ $idx === 0 ? 'opacity-0' : ($idx <= $currentIdx ? 'bg-emerald-500' : 'bg-zinc-200 dark:bg-zinc-700') }}"></div>

                                <button type="button" wire:click="jumpTo('{{ $s['id'] }}')"
                                        @if($current) aria-current="step" @endif
                                        title="{{ $s['label'] }}"
                                        class="shrink-0 mx-1 w-7 h-7 rounded-full border flex items-center justify-center text-xs font-semibold transition
                                            {{ $done
                                                ? 'bg-emerald-600 border-emerald-600 text-white hover:bg-emerald-700'
                                                : ($current
                                                    ? 'bg-emerald-600 border-emerald-600 text-white ring-4 ring-emerald-100 dark:ring-emerald-900/40 cursor-default'
                                                    : 'bg-white dark:bg-zinc-900 border-zinc-300 dark:border-zinc-600 text-zinc-400 dark:text-zinc-500 hover:border-emerald-400 dark:hover:border-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400') }}">
                                    @if($done)
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    @else
                                        {{ $idx + 1 }}
                                    @endif
                                </button>

                                {{-- right connector --}}
                                <div class="h-0.5 flex-1 rounded-full {{ $idx === count($navSteps) - 1 ? 'opacity-0' : ($idx < $currentIdx ? 'bg-emerald-500' : 'bg-zinc-200 dark:bg-zinc-700') }}"></div>
                            </div>
                            <span class="hidden md:block mt-2 text-[11px] whitespace-nowrap transition-colors
                                {{ $current
                                    ? 'text-zinc-900 dark:text-zinc-50 font-semibold'
                                    : ($done ? 'text-zinc-600 dark:text-zinc-400' : 'text-zinc-400 dark:text-zinc-600') }}">
                                {{ $s['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- Current step name: mobile only (nodes are label-less on mobile) --}}
                <p class="md:hidden mt-2.5 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400">
                    {{ __('Step :n of :total', ['n' => $currentIdx + 1, 'total' => count($navSteps)]) }}
                    <span class="text-zinc-900 dark:text-zinc-50">· {{ $navSteps[$currentIdx]['label'] ?? '' }}</span>
                </p>
            </div>
        </div>
    @endif

    {{-- Stage --}}
    <main class="relative z-10 px-6 md:px-8 pb-16 pt-6">
        <div class="max-w-[560px] mx-auto" wire:key="{{ $step }}">

            {{-- ── WELCOME ── --}}
            @if($step === 'welcome')
                <div class="step-in">
                    <div class="mb-8">
                        <x-brand-ring :size="56" :stroke="6" :progress="1" color="#059669" track-color="#d1fae5" />
                    </div>
                    <h1 class="font-serif text-4xl md:text-5xl text-zinc-900 dark:text-zinc-50 leading-tight tracking-tight mb-4">
                        {{ __('Welcome') }}, {{ $name }}.
                        <br>{{ __("Let's set your") }}
                        <em class="text-emerald-600 not-italic">{{ __('daily target.') }}</em>
                    </h1>
                    <p class="text-zinc-500 dark:text-zinc-400 text-base leading-relaxed mb-10 max-w-sm">
                        {{ __('Calorie Tracker turns a sentence — "chicken bowl, large" — into calories and macros. First, two minutes to set your goal.') }}
                    </p>

                    <div class="space-y-3">
                        {{-- AI path --}}
                        <button wire:click="chooseAi"
                                class="w-full flex items-center justify-between gap-4 px-5 py-4 rounded-xl bg-zinc-900 dark:bg-zinc-50 text-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-200 transition group">
                            <div class="text-start">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-sm font-semibold">{{ __('Set up with AI') }}</span>
                                    <span class="font-mono text-[9px] font-semibold uppercase tracking-widest bg-emerald-500 text-white px-2 py-0.5 rounded-full">{{ __('Recommended') }}</span>
                                </div>
                                <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ __('A few quick questions. We calculate the rest.') }}</p>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 opacity-60 group-hover:translate-x-0.5 transition-transform">
                                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </button>

                        {{-- Manual path --}}
                        <button wire:click="chooseManual"
                                class="w-full flex items-center justify-between gap-4 px-5 py-4 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600 hover:shadow-sm transition group">
                            <div class="text-start">
                                <p class="text-sm font-semibold mb-0.5">{{ __("I'll configure it myself") }}</p>
                                <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ __('Go to profile and enter targets manually.') }}</p>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 opacity-40 group-hover:translate-x-0.5 transition-transform">
                                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </button>
                    </div>
                </div>

            {{-- ── DETAILS ── --}}
            @elseif($step === 'details')
                <div class="step-in">
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-2">{{ __('A few measurements') }}</p>
                    <h1 class="font-serif text-3xl md:text-4xl text-zinc-900 dark:text-zinc-50 tracking-tight mb-2">{{ __('Tell us about you.') }}</h1>
                    <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-8">{{ __('Used once to calculate your baseline — never shared or used elsewhere.') }}</p>

                    <div class="grid grid-cols-2 gap-4 mb-8">
                        {{-- Name (full width) --}}
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1.5">{{ __('Name') }}</label>
                            <input wire:model="name" type="text"
                                   class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 focus:outline-none transition" />
                            @error('name') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Age --}}
                        <div>
                            <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1.5">{{ __('Age') }}</label>
                            <div class="relative">
                                <input wire:model="age" type="number" min="13" max="100"
                                       class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2.5 pr-10 text-sm font-mono text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 focus:outline-none transition" />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-zinc-400 pointer-events-none">{{ __('yrs') }}</span>
                            </div>
                            @error('age') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Sex segmented --}}
                        <div>
                            <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1.5">{{ __('Biological sex') }}</label>
                            <div class="flex rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 p-0.5 gap-0.5">
                                <button type="button" wire:click="selectSex('F')"
                                        class="flex-1 py-2 rounded-md text-xs font-semibold transition {{ $sex === 'F' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700' }}">
                                    {{ __('Female') }}
                                </button>
                                <button type="button" wire:click="selectSex('M')"
                                        class="flex-1 py-2 rounded-md text-xs font-semibold transition {{ $sex === 'M' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700' }}">
                                    {{ __('Male') }}
                                </button>
                            </div>
                            @error('sex') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Weight --}}
                        <div>
                            <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1.5">{{ __('Current weight') }}</label>
                            <div class="relative">
                                <input wire:model="weightKg" type="number" min="30" max="300" step="0.1"
                                       class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2.5 pr-8 text-sm font-mono text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 focus:outline-none transition" />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-zinc-400 pointer-events-none">{{ __('kg') }}</span>
                            </div>
                            @error('weightKg') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Height --}}
                        <div>
                            <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1.5">{{ __('Height') }}</label>
                            <div class="relative">
                                <input wire:model="heightCm" type="number" min="100" max="230"
                                       class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2.5 pr-8 text-sm font-mono text-zinc-900 dark:text-zinc-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 focus:outline-none transition" />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-zinc-400 pointer-events-none">{{ __('cm') }}</span>
                            </div>
                            @error('heightCm') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button wire:click="next"
                            class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-sm shadow-emerald-600/20">
                        {{ __('Continue') }}
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                </div>

            {{-- ── ACTIVITY ── --}}
            @elseif($step === 'activity')
                <div class="step-in">
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-2">{{ __('Activity level') }}</p>
                    <h1 class="font-serif text-3xl md:text-4xl text-zinc-900 dark:text-zinc-50 tracking-tight mb-2">{{ __('How active are you?') }}</h1>
                    <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-8">{{ __("Roughly. We'll adjust as you log meals and weight over time.") }}</p>

                    @php
                        $activities = [
                            ['id' => 'sedentary', 'title' => __('Sedentary'),         'sub' => __('Desk job, little or no exercise.'),                 'mult' => '×1.2',   'icon' => 'M12 5a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM9 7h6v6h-1v5h-1v-5h-2v5h-1v-5H9V7z'],
                            ['id' => 'light',     'title' => __('Lightly active'),    'sub' => __('Light walks or 1–2 workouts per week.'),            'mult' => '×1.375', 'icon' => 'M13 3a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM10 21l2-7-3-2 2-5 4 2 3 3M9 14l-2 4'],
                            ['id' => 'moderate',  'title' => __('Moderately active'), 'sub' => __('Workouts 3–5 days a week, mostly on your feet.'),   'mult' => '×1.55',  'icon' => 'M14 3a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM9 21l3-7-3-3 3-4 4 3-2 3 3 5M6 12l3-1'],
                            ['id' => 'very',      'title' => __('Very active'),       'sub' => __('Hard training 6–7 days a week or a physical job.'), 'mult' => '×1.725', 'icon' => 'M15 3a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM6 12l3 1 2-3 3 2-2 4 3 5M14 9l3-2 3 1M4 20l4-2'],
                        ];
                    @endphp

                    <div class="space-y-2.5 mb-8">
                        @foreach($activities as $opt)
                            <button type="button" wire:click="selectActivity('{{ $opt['id'] }}')"
                                    class="w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border text-start transition hover:-translate-y-px
                                        {{ $activity === $opt['id']
                                            ? 'border-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 ring-2 ring-emerald-500/10'
                                            : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:shadow-sm' }}">
                                <div class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0 transition
                                    {{ $activity === $opt['id'] ? 'bg-emerald-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400' }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="{{ $opt['icon'] }}" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $opt['title'] }}</span>
                                        <span class="font-mono text-[10px] text-zinc-400 dark:text-zinc-500">{{ $opt['mult'] }}</span>
                                    </div>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">{{ $opt['sub'] }}</p>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition
                                    {{ $activity === $opt['id'] ? 'border-emerald-600 bg-emerald-600' : 'border-zinc-200 dark:border-zinc-600' }}">
                                    @if($activity === $opt['id'])
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                    @error('activity') <p class="mb-4 text-xs text-rose-500">{{ $message }}</p> @enderror

                    <div class="flex items-center gap-3">
                        <button wire:click="back" class="flex items-center gap-1.5 text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            {{ __('Back') }}
                        </button>
                        <button wire:click="next"
                                class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-sm shadow-emerald-600/20 disabled:opacity-40"
                                @disabled(!$activity)>
                            {{ __('Continue') }}
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </button>
                    </div>
                </div>

            {{-- ── GOAL ── --}}
            @elseif($step === 'goal')
                <div class="step-in">
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-2">{{ __('Your goal') }}</p>
                    <h1 class="font-serif text-3xl md:text-4xl text-zinc-900 dark:text-zinc-50 tracking-tight mb-2">{{ __('What are you aiming for?') }}</h1>
                    <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-8">{{ __('Shapes your daily calorie target and macro split. Change it anytime in Profile.') }}</p>

                    @php
                        $goals = [
                            ['id' => 'lose',     'title' => __('Lose weight'),   'sub' => __('A gentle calorie deficit — about 0.5 kg per week.'), 'delta' => '−500 kcal/day', 'icon' => 'M5 9l7 7 7-7M5 5h14'],
                            ['id' => 'maintain', 'title' => __('Maintain'),      'sub' => __('Hold steady at your current weight.'),               'delta' => '0',            'icon' => 'M4 9h16M4 15h16'],
                            ['id' => 'build',    'title' => __('Build muscle'),  'sub' => __('A small surplus paired with strength training.'),    'delta' => '+300 kcal/day', 'icon' => 'M5 15l7-7 7 7M5 19h14'],
                        ];
                    @endphp

                    <div class="space-y-2.5 mb-8">
                        @foreach($goals as $opt)
                            <button type="button" wire:click="selectGoal('{{ $opt['id'] }}')"
                                    class="w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border text-start transition hover:-translate-y-px
                                        {{ $goal === $opt['id']
                                            ? 'border-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 ring-2 ring-emerald-500/10'
                                            : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:shadow-sm' }}">
                                <div class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0 transition
                                    {{ $goal === $opt['id'] ? 'bg-emerald-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400' }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="{{ $opt['icon'] }}" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $opt['title'] }}</p>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">{{ $opt['sub'] }}</p>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition
                                    {{ $goal === $opt['id'] ? 'border-emerald-600 bg-emerald-600' : 'border-zinc-200 dark:border-zinc-600' }}">
                                    @if($goal === $opt['id'])
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    @endif
                                </div>
                            </button>
                        @endforeach

                        {{-- Something else --}}
                        <button type="button" wire:click="selectGoal('other')"
                                class="w-full flex items-center gap-4 px-4 py-3.5 rounded-xl border text-start transition hover:-translate-y-px
                                    {{ $goal === 'other'
                                        ? 'border-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 ring-2 ring-emerald-500/10'
                                        : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 hover:shadow-sm' }}">
                            <div class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0 transition
                                {{ $goal === 'other' ? 'bg-emerald-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400' }}">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ __('Something else') }}</p>
                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">{{ __('Body recomp, performance, or anything in between.') }}</p>
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition
                                {{ $goal === 'other' ? 'border-emerald-600 bg-emerald-600' : 'border-zinc-200 dark:border-zinc-600' }}">
                                @if($goal === 'other')
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </div>
                        </button>

                        {{-- Free-text field, shown when "Something else" is selected --}}
                        @if($goal === 'other')
                            <div class="pt-1">
                                <textarea wire:model.live.debounce.300ms="goalNotes"
                                          rows="3"
                                          placeholder="{{ __('e.g. lose fat while building muscle, train for a marathon, recover after injury...') }}"
                                          class="w-full px-4 py-3 text-sm rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none transition"
                                ></textarea>
                                @error('goalNotes') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                    @error('goal') <p class="mb-4 text-xs text-rose-500">{{ $message }}</p> @enderror

                    <div class="flex items-center gap-3">
                        <button wire:click="back" class="flex items-center gap-1.5 text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            {{ __('Back') }}
                        </button>
                        <button wire:click="next"
                                class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-sm shadow-emerald-600/20 disabled:opacity-40"
                                @disabled(!$goal || ($goal === 'other' && !trim($goalNotes)))>
                            {{ __('Continue') }}
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </button>
                    </div>
                </div>

            {{-- ── EATING HABITS ── --}}
            @elseif($step === 'eating')
                <div class="step-in">
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-1">{{ __('Eating habits') }}</p>
                    <h1 class="text-3xl md:text-4xl font-serif text-zinc-900 dark:text-zinc-50 mb-2 leading-tight">{{ __('How do you usually eat?') }}</h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-8">{{ __('Helps the AI set a more accurate target for your lifestyle.') }}</p>

                    <div class="space-y-3 mb-8">
                        @foreach([
                            ['id' => 'home', 'icon' => 'home', 'title' => __('Cook at home'),   'sub' => __('You control ingredients and portions most of the time.')],
                            ['id' => 'out',  'icon' => 'fork', 'title' => __('Mostly eat out'), 'sub' => __('Restaurants, takeaway, or delivery most days.')],
                            ['id' => 'mix',  'icon' => 'mix',  'title' => __('Mix of both'),    'sub' => __('Some home cooking, some eating out.')],
                        ] as $opt)
                            <button wire:click="selectEatingHabit('{{ $opt['id'] }}')"
                                    class="w-full flex items-center gap-4 p-4 rounded-2xl border-2 transition text-left
                                        {{ $eatingHabit === $opt['id']
                                            ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30'
                                            : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 hover:border-zinc-300 dark:hover:border-zinc-600' }}">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                                    {{ $eatingHabit === $opt['id'] ? 'bg-emerald-600' : 'bg-zinc-200 dark:bg-zinc-700' }}">
                                    @if($opt['icon'] === 'home')
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $eatingHabit === $opt['id'] ? '#fff' : 'currentColor' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="{{ $eatingHabit !== $opt['id'] ? 'text-zinc-500 dark:text-zinc-400' : '' }}"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    @elseif($opt['icon'] === 'fork')
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $eatingHabit === $opt['id'] ? '#fff' : 'currentColor' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="{{ $eatingHabit !== $opt['id'] ? 'text-zinc-500 dark:text-zinc-400' : '' }}"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 002-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 00-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
                                    @else
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $eatingHabit === $opt['id'] ? '#fff' : 'currentColor' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="{{ $eatingHabit !== $opt['id'] ? 'text-zinc-500 dark:text-zinc-400' : '' }}"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-50">{{ $opt['title'] }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $opt['sub'] }}</p>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0
                                    {{ $eatingHabit === $opt['id'] ? 'border-emerald-600 bg-emerald-600' : 'border-zinc-300 dark:border-zinc-600' }}">
                                    @if($eatingHabit === $opt['id'])
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-3">
                        <button wire:click="back" class="flex items-center gap-1.5 text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            {{ __('Back') }}
                        </button>
                        <button wire:click="next"
                                class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-sm shadow-emerald-600/20 disabled:opacity-40"
                                @disabled(!$eatingHabit)>
                            {{ __('Continue') }}
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </button>
                    </div>
                </div>

            {{-- ── HEALTH CONTEXT ── --}}
            @elseif($step === 'context')
                <div class="step-in">
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-1">{{ __('Almost there') }}</p>
                    <h1 class="text-3xl md:text-4xl font-serif text-zinc-900 dark:text-zinc-50 mb-2 leading-tight">{{ __('Anything else we should know?') }}</h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-8">{{ __('Optional — but the more context the AI has, the better your target.') }}</p>

                    <div class="mb-4">
                        <textarea
                            wire:model="healthNotes"
                            rows="5"
                            placeholder="{{ __('e.g. type 2 diabetes, vegetarian, food allergies, bad knees, shift work, high stress, poor sleep...') }}"
                            class="w-full px-4 py-3 text-sm rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none transition"
                        ></textarea>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-2">{{ __('This stays private and is only used to personalise your calorie target.') }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button wire:click="back" class="flex items-center gap-1.5 text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            {{ __('Back') }}
                        </button>
                        <button wire:click="next"
                                class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-sm shadow-emerald-600/20">
                            {{ __('Calculate my target') }}
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </button>
                    </div>
                </div>

            {{-- ── CALCULATING ── --}}
            @elseif($step === 'calc')
                <div class="step-in flex flex-col items-center text-center py-12"
                     x-data x-init="setTimeout(() => $wire.proceedAfterCalc(), 1700)">
                    <div class="relative mb-6">
                        <x-brand-ring :size="200" :stroke="12" :progress="0.65" color="#059669" track-color="#d1fae5" :pulse="true" />
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5z"/>
                                <path d="M19 14l.7 2.1L22 17l-2.3.9L19 20l-.7-2.1L16 17l2.3-.9z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="font-mono text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                        {{ __('CALCULATING') }}
                        <span x-data="{ d: 0 }" x-init="setInterval(() => d = (d+1)%4, 400)"
                              x-text="['', '●', '●●', '●●●'][d]" class="inline-block w-6 text-start"></span>
                    </p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-600 mt-3 max-w-xs">
                        {{ __('Mifflin–St Jeor for BMR, your activity multiplier on top, then a goal-adjusted deficit or surplus.') }}
                    </p>
                </div>

            {{-- ── RESULT ── --}}
            @elseif($step === 'result')
                @php $final = $target + $adjust; @endphp
                <div class="step-in">
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-6 text-center">{{ __('Your daily target') }}</p>

                    {{-- Ring --}}
                    <div class="flex flex-col items-center mb-6">
                        <div class="relative">
                            <x-brand-ring :size="200" :stroke="12" :progress="1" color="#059669" track-color="#d1fae5" />
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="font-mono text-4xl font-bold text-zinc-900 dark:text-zinc-50 tabular-nums tracking-tight leading-none">
                                    {{ number_format($final) }}
                                </span>
                                <span class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">{{ __('kcal / day') }}</span>
                            </div>
                        </div>

                        {{-- Adjust pill --}}
                        <div class="mt-4 inline-flex items-center gap-3 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-full px-3 py-2 shadow-sm">
                            <button wire:click="nudgeTarget(-100)"
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 min-w-[80px] text-center">
                                @if($adjust === 0) {{ __('Adjust ±100') }}
                                @elseif($adjust > 0) {{ __('Adjusted') }} +{{ $adjust }}
                                @else {{ __('Adjusted') }} {{ $adjust }}
                                @endif
                            </span>
                            <button wire:click="nudgeTarget(100)"
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>

                        @if($previousTarget > 0 && $previousTarget !== $final)
                            <p class="mt-4 inline-flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                                <span>{{ __('Previous target') }}</span>
                                <span class="font-mono">{{ number_format($previousTarget) }}</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-zinc-400 dark:text-zinc-500 rtl:-scale-x-100" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                <span class="font-mono font-semibold text-zinc-900 dark:text-zinc-50">{{ number_format($final) }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- Macro split --}}
                    @php
                        $macroTotal = $protein + $carbs + $fat;
                    @endphp
                    <div class="grid grid-cols-3 gap-2.5 mb-5">
                        @foreach([
                            ['label' => 'P', 'name' => __('Protein'), 'val' => $protein, 'color' => '#f43f5e', 'bg' => 'bg-rose-500'],
                            ['label' => 'C', 'name' => __('Carbs'),   'val' => $carbs,   'color' => '#4f46e5', 'bg' => 'bg-indigo-500'],
                            ['label' => 'F', 'name' => __('Fat'),     'val' => $fat,     'color' => '#f59e0b', 'bg' => 'bg-amber-400'],
                        ] as $m)
                            <div class="rounded-xl border border-zinc-100 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-3">
                                <div class="flex items-center gap-1 mb-2">
                                    <span class="text-xs font-bold" style="color: {{ $m['color'] }}">{{ $m['label'] }}</span>
                                    <span class="text-xs text-zinc-400">{{ $m['name'] }}</span>
                                </div>
                                <p class="font-mono text-xl font-bold text-zinc-900 dark:text-zinc-50 leading-none mb-2">
                                    {{ $m['val'] }}<small class="text-xs font-normal text-zinc-400 ms-0.5">g</small>
                                </p>
                                <div class="h-1 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                                    <div class="{{ $m['bg'] }} h-full rounded-full transition-all duration-700"
                                         style="width: {{ $macroTotal > 0 ? round($m['val'] / $macroTotal * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Explanation --}}
                    <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 mb-6">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0">
                            <path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5z"/>
                        </svg>
                        <p class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed">
                            @if($aiExplanation)
                                {{ $aiExplanation }}
                            @else
                                {{ __('From :bmr kcal baseline (Mifflin–St Jeor) × :activity activity = :tdee kcal to maintain. Adjusted for your goal.', ['bmr' => number_format($bmr), 'activity' => $activityName, 'tdee' => number_format($tdee)]) }}
                            @endif
                        </p>
                    </div>

                    {{-- Review summary — recap the inputs so saving is a deliberate confirm --}}
                    @php
                        $sexLabel    = ['F' => __('Female'), 'M' => __('Male')][$sex] ?? $sex;
                        $goalLabel   = ['lose' => __('Lose weight'), 'maintain' => __('Maintain'), 'build' => __('Build muscle'), 'other' => __('Custom')][$goal] ?? $goal;
                        if ($goal === 'other' && $goalNotes) { $goalLabel = \Illuminate\Support\Str::limit($goalNotes, 40); }
                        $eatingLabel = ['home' => __('Cook at home'), 'out' => __('Mostly eat out'), 'mix' => __('Mix of both')][$eatingHabit] ?? $eatingHabit;
                        $summaryRows = array_values(array_filter([
                            $age          ? ['label' => __('Age'),            'value' => $age . ' ' . __('yrs')]     : null,
                            $sex          ? ['label' => __('Biological sex'), 'value' => $sexLabel]                  : null,
                            $weightKg     ? ['label' => __('Current weight'), 'value' => $weightKg . ' ' . __('kg')] : null,
                            $heightCm     ? ['label' => __('Height'),         'value' => $heightCm . ' ' . __('cm')] : null,
                            $activityName ? ['label' => __('Activity level'), 'value' => $activityName]              : null,
                            $goal         ? ['label' => __('Your goal'),      'value' => $goalLabel]                 : null,
                            $eatingHabit  ? ['label' => __('Eating habits'),  'value' => $eatingLabel]               : null,
                        ]));
                    @endphp
                    @if(count($summaryRows))
                        <div class="mb-6">
                            <p class="text-xs font-semibold uppercase tracking-widest rtl:tracking-normal text-emerald-600 dark:text-emerald-400 mb-3">{{ __('Based on your answers') }}</p>
                            <dl class="rounded-xl border border-zinc-100 dark:border-zinc-800 divide-y divide-zinc-100 dark:divide-zinc-800 overflow-hidden">
                                @foreach($summaryRows as $row)
                                    <div class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm">
                                        <dt class="text-zinc-500 dark:text-zinc-400 shrink-0">{{ $row['label'] }}</dt>
                                        <dd class="min-w-0 truncate text-end font-medium text-zinc-900 dark:text-zinc-50">{{ $row['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif

                    {{-- Confirm — sticky so the save action stays visible while reviewing --}}
                    <div class="sticky bottom-0 -mx-6 px-6 pb-4 pt-8 bg-gradient-to-t from-white via-white/95 to-transparent dark:from-zinc-950 dark:via-zinc-950/95">
                        <button wire:click="confirm"
                                class="w-full flex items-center justify-center gap-2 px-5 py-3.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-lg shadow-emerald-600/25 mb-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            {{ $previousTarget > 0 ? __('Save target') : __('Start tracking') }}
                        </button>
                        <div class="text-center">
                            <button wire:click="back" class="text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition">
                                ← {{ __('Back') }}
                            </button>
                        </div>
                    </div>
                </div>

            {{-- ── MANUAL EXIT ── --}}
            @elseif($step === 'manual')
                <div class="step-in flex flex-col items-center text-center py-12">
                    <div class="relative mb-6">
                        <x-brand-ring :size="160" :stroke="10" :progress="0.4" color="#3f3f46" track-color="#e4e4e7" />
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#71717a" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                        </div>
                    </div>
                    <h2 class="font-serif text-2xl text-zinc-900 dark:text-zinc-50 mb-2">{{ __('Configure it your way.') }}</h2>
                    <p class="text-sm text-zinc-400 dark:text-zinc-500 max-w-xs mb-8">
                        {{ __("We'll drop you into your profile. Set your daily calorie target manually — change it anytime.") }}
                    </p>
                    <div class="flex items-center gap-3">
                        <button wire:click="back"
                                class="flex items-center gap-1.5 text-sm text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            {{ __('Change my mind') }}
                        </button>
                        <button wire:click="chooseManual"
                                class="flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition">
                            {{ __('Take me to profile') }}
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </button>
                    </div>
                </div>

            {{-- ── DONE ── --}}
            @elseif($step === 'done')
                @php $finalTarget = $target + $adjust; @endphp
                <div class="step-in flex flex-col items-center text-center py-12"
                     x-data x-init="setTimeout(() => $wire.goHome(), 1200)">
                    <div class="relative mb-6">
                        <x-brand-ring :size="200" :stroke="12" :progress="1" color="#059669" track-color="#d1fae5" />
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                    </div>
                    <h2 class="font-serif text-3xl text-zinc-900 dark:text-zinc-50 mb-2">{{ __("You're set,") }} {{ explode(' ', $name)[0] }}.</h2>
                    <p class="text-sm text-zinc-400 dark:text-zinc-500">
                        {{ __('Daily target locked at') }} <span class="font-mono font-semibold text-zinc-700 dark:text-zinc-300">{{ number_format($finalTarget) }} {{ __('kcal') }}</span>.
                        {{ __("Log your first meal whenever you're ready.") }}
                    </p>
                </div>

            @endif

        </div>
    </main>


</div>
