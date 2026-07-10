<div class="mx-auto max-w-5xl px-6 md:px-10 py-10 md:py-14">

    {{-- ── Header ── --}}
    <div class="flex items-start justify-between gap-4 mb-10">
        <div class="min-w-0">
            <h1 class="font-serif text-3xl md:text-4xl text-zinc-900 dark:text-zinc-50 leading-tight tracking-tight">{{ __('Your progress') }}</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ now()->locale(app()->getLocale())->translatedFormat(app()->getLocale() === 'ar' ? 'l، j F' : 'l, F j') }}</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            @if($streak > 0)
                <span class="hidden sm:inline-flex items-center gap-1.5 text-sm font-medium text-amber-600 dark:text-amber-400 whitespace-nowrap"><x-flame />{{ __(':count-day streak', ['count' => $streak]) }}</span>
            @endif
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-600/20 whitespace-nowrap">
                {{ __('Log a meal') }}
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>

    {{-- ── Hero: today ── --}}
    @php
        $fillPct   = min(100, $todayPct);
        $over      = $todayCalories - $dailyGoal;
        $ringColor = $todayPct >= 100 ? '#f43f5e' : ($todayPct >= 80 ? '#f59e0b' : '#059669');
    @endphp
    <div class="flex items-center gap-6 md:gap-8 pb-10 mb-10 border-b border-zinc-200/70 dark:border-zinc-800/80">
        <div class="relative w-28 h-28 md:w-32 md:h-32 shrink-0">
            <svg class="-rotate-90 w-full h-full" viewBox="0 0 72 72">
                <circle cx="36" cy="36" r="30" fill="none" stroke-width="6" class="stroke-zinc-200 dark:stroke-zinc-800" />
                <circle cx="36" cy="36" r="30" fill="none" stroke-width="6" stroke-linecap="round"
                        stroke="{{ $ringColor }}" stroke-dasharray="188.5"
                        stroke-dashoffset="{{ 188.5 * (1 - $fillPct / 100) }}"
                        style="transition: stroke-dashoffset .7s cubic-bezier(.4,0,.2,1)" />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="font-mono text-xl md:text-2xl font-bold text-zinc-900 dark:text-zinc-50 leading-none">{{ $todayPct }}<span class="text-sm font-normal text-zinc-500 dark:text-zinc-400">%</span></span>
                <span class="text-[10px] uppercase tracking-wider rtl:tracking-normal text-zinc-500 dark:text-zinc-400 mt-1">{{ __('of goal') }}</span>
            </div>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-widest rtl:tracking-normal text-emerald-600 dark:text-emerald-400 mb-2">{{ __('Eaten today') }}</p>
            <p class="font-mono text-4xl md:text-5xl font-bold text-zinc-900 dark:text-zinc-50 leading-none">
                {{ number_format($todayCalories) }}<span class="text-base font-normal text-zinc-500 dark:text-zinc-300 ms-1.5">/ {{ number_format($dailyGoal) }}</span>
            </p>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm text-zinc-500 dark:text-zinc-400 mt-3">
                @if($over > 0)
                    <span class="font-medium text-rose-500 dark:text-rose-400">{{ number_format($over) }} {{ __('kcal over') }}</span>
                @else
                    <span><span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($todayRemaining) }}</span> {{ __('kcal left') }}</span>
                @endif
                @if($todayProtein || $todayCarbs || $todayFat)
                    <x-macros :protein="$todayProtein" :carbs="$todayCarbs" :fat="$todayFat" class="text-xs" />
                @endif
            </div>
        </div>
    </div>

    @php $goalTop = $chartMax > 0 ? (1 - $dailyGoal / $chartMax) * 100 : 0; @endphp

    {{-- ── Row 1: Overview (weekly bar chart with week navigator) ── --}}
    <div class="mb-14">
        <div class="flex items-center justify-between gap-3 flex-wrap mb-6">
            <h2 class="font-serif text-2xl md:text-[1.75rem] text-zinc-900 dark:text-zinc-50 tracking-tight leading-tight">{{ __('Overview') }}</h2>
            <div class="flex items-center gap-2">
                <button wire:click="prevWeek" title="{{ __('Previous week') }}"
                        class="w-7 h-7 inline-flex items-center justify-center rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rtl:-scale-x-100"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <span class="text-sm text-zinc-700 dark:text-zinc-200 min-w-[124px] text-center">{{ $weekLabel }}</span>
                <button wire:click="nextWeek" @disabled(!$canGoNext) title="{{ __('Next week') }}"
                        class="w-7 h-7 inline-flex items-center justify-center rounded-md text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-700 dark:hover:text-zinc-200 transition disabled:opacity-30 disabled:hover:bg-transparent disabled:cursor-not-allowed">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rtl:-scale-x-100"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>

        <div class="flex justify-end mb-4">
            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('avg') }} <span class="font-mono text-zinc-600 dark:text-zinc-300">{{ number_format($weeklyAverage) }}</span> {{ __('kcal/day') }}
            </span>
        </div>

        {{-- Value labels --}}
        <div class="flex justify-between gap-2 md:gap-3 mb-2">
            @foreach($weeklyData as $day)
                <span class="flex-1 text-center font-mono text-xs text-zinc-500 dark:text-zinc-300 {{ $day['calories'] === 0 ? 'invisible' : '' }}">
                    {{ $day['calories'] >= 1000 ? number_format($day['calories'] / 1000, 1) . 'k' : $day['calories'] }}
                </span>
            @endforeach
        </div>

        {{-- Bars band + goal line --}}
        <div class="relative flex items-end justify-between gap-2 md:gap-3 h-44">
            <div class="absolute inset-x-0 z-10 pointer-events-none border-t border-dashed border-zinc-300 dark:border-zinc-600" style="top: {{ $goalTop }}%"></div>
            @if($weeklyData->sum('calories') === 0)
                <div class="absolute inset-0 z-10 flex flex-col items-center justify-center text-center gap-1.5">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Nothing logged this week yet.') }}</p>
                    <a href="{{ route('home') }}" class="text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition">
                        {{ __('Log your first meal to see your week') }} →
                    </a>
                </div>
            @endif
            @foreach($weeklyData as $index => $day)
                @php
                    $barColor = $day['pct'] >= 100
                        ? 'bg-rose-400 dark:bg-rose-500'
                        : ($day['pct'] >= 80 ? 'bg-amber-400 dark:bg-amber-500' : 'bg-emerald-500');
                    $barH = $chartMax > 0 ? ($day['calories'] / $chartMax) * 100 : 0;
                @endphp
                <button type="button" wire:click="selectDay('{{ $day['date'] }}')"
                        title="{{ $day['isToday'] ? __('Today') : $day['label'] }}"
                        class="group relative flex-1 h-full flex items-end cursor-pointer">
                    @if($day['calories'] > 0)
                        <div class="pointer-events-none absolute left-1/2 -translate-x-1/2 z-20 hidden group-hover:block whitespace-nowrap rounded-lg bg-zinc-900 dark:bg-zinc-800 ring-1 ring-white/10 px-2.5 py-1.5 text-center shadow-lg"
                             style="bottom: calc({{ $barH }}% + 8px)">
                            <p class="font-mono text-xs font-semibold text-white">{{ number_format($day['calories']) }} {{ __('kcal') }}</p>
                            <x-macros :protein="$day['protein']" :carbs="$day['carbs']" :fat="$day['fat']" class="text-[10px] mt-1 justify-center" />
                        </div>
                    @endif
                    <div class="bar-animate w-full rounded-t-md {{ $barColor }} {{ $day['calories'] === 0 ? 'bg-zinc-100 dark:bg-zinc-800/80 rounded-md' : '' }} {{ $day['date'] === $selectedDate ? 'ring-2 ring-offset-2 ring-zinc-900 dark:ring-white ring-offset-white dark:ring-offset-zinc-950' : 'group-hover:opacity-90' }}"
                         style="height: {{ max(3, $barH) }}%; animation-delay: {{ $index * 0.05 }}s"></div>
                </button>
            @endforeach
        </div>

        {{-- Day labels --}}
        <div class="flex justify-between gap-2 md:gap-3 mt-2">
            @foreach($weeklyData as $day)
                <span class="flex-1 text-center text-xs {{ $day['isToday'] ? 'font-semibold text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400' }}">
                    {{ $day['isToday'] ? __('Today') : $day['label'] }}
                </span>
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-zinc-500 dark:text-zinc-400">
            <span class="flex items-center gap-1.5"><span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span> {{ __('Under goal') }}</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400 dark:bg-amber-500"></span> {{ __('Near goal (≥80%)') }}</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-2.5 h-2.5 rounded-full bg-rose-400 dark:bg-rose-500"></span> {{ __('Over goal') }}</span>
        </div>
    </div>

    {{-- ── Row 2: History ── --}}
    <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800/60">
        <div class="flex items-center justify-between gap-3 flex-wrap mb-5 pt-8">
            <h2 class="font-serif text-2xl md:text-[1.75rem] text-zinc-900 dark:text-zinc-50 tracking-tight leading-tight">{{ __('History') }}</h2>
            <div class="flex items-center gap-1.5 flex-wrap">
                <button wire:click="setFilter('week')"
                        class="px-3 py-1 rounded-full text-xs font-medium transition whitespace-nowrap
                            {{ $historyFilter === 'week' && $weekOffset === 0
                                ? 'bg-emerald-600 text-white'
                                : 'text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 hover:border-emerald-400 dark:hover:border-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                    {{ __('Last 7 days') }}
                </button>
                <button wire:click="setFilter('all')"
                        class="px-3 py-1 rounded-full text-xs font-medium transition whitespace-nowrap
                            {{ $historyFilter === 'all'
                                ? 'bg-emerald-600 text-white'
                                : 'text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 hover:border-emerald-400 dark:hover:border-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                    {{ __('All') }}
                </button>

                <span class="w-px h-4 bg-zinc-200 dark:bg-zinc-700 mx-1" aria-hidden="true"></span>

                <input type="date" wire:model.live="jumpDate" max="{{ today()->toDateString() }}"
                       class="text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-transparent px-2.5 py-1 text-zinc-600 dark:text-zinc-300 focus:outline-none focus:border-emerald-400 dark:focus:border-emerald-500 transition [color-scheme:light] dark:[color-scheme:dark]"
                       aria-label="{{ __('Jump to date') }}" />
                @if($isDayView)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-600 text-white whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($selectedDate)->locale(app()->getLocale())->translatedFormat(app()->getLocale() === 'ar' ? 'l، j M' : 'l, M j') }}
                        <button wire:click="setFilter('week')" class="hover:opacity-70" title="{{ __('Clear') }}">✕</button>
                    </span>
                @endif
            </div>
        </div>

        @if($recentEntries->isEmpty())
            <div class="py-16 text-center">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    @if($isDayView) {{ __('No meals logged on this day.') }}
                    @elseif($historyFilter === 'all') {{ __('No meals logged yet.') }}
                    @else {{ __('No meals logged this week.') }}
                    @endif
                </p>
                @if($historyFilter !== 'all')
                    <a href="{{ route('home') }}" class="inline-block mt-2 text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition">
                        {{ __('Log a meal') }} →
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12">
                @foreach($recentEntries as $dateString => $entries)
                    @php
                        $date     = \Carbon\Carbon::parse($dateString);
                        $dayLabel = $date->isToday() ? __('Today') : ($date->isYesterday() ? __('Yesterday') : $date->locale(app()->getLocale())->translatedFormat(app()->getLocale() === 'ar' ? 'l، j M' : 'l, M j'));
                        $dayTotal = $entries->sum('calories');
                    @endphp

                    <div class="mb-8">
                        <div class="flex items-baseline justify-between mb-1">
                            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $dayLabel }}</h3>
                            <span class="font-mono text-xs text-zinc-500 dark:text-zinc-400">{{ number_format($dayTotal) }} {{ __('kcal') }}</span>
                        </div>

                        @foreach($entries as $entry)
                            <div wire:key="{{ $entry->id }}" class="border-t border-zinc-100 dark:border-zinc-800/70 py-3">
                                <x-entry-row :entry="$entry" :editing="$editingId === $entry->id" />
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            @if($hasMoreDays)
                <div class="text-center pt-1">
                    <button wire:click="showMore"
                            wire:loading.attr="disabled"
                            wire:target="showMore"
                            class="inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 dark:text-zinc-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition">
                        {{ __('Show earlier meals') }}
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                </div>
            @endif
        @endif

        <x-confirm-delete-modal :id="$confirmingDeleteId" :food="$confirmingDeleteFood" />
    </div>

</div>
