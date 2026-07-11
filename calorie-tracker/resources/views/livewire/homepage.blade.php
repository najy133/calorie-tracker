<div class="mx-auto max-w-2xl px-6 md:px-10 py-10 md:py-14">

    {{-- ── Header ── --}}
    @php
        $hour = now()->hour;
        $greetingKey = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    @endphp
    <div class="flex items-start justify-between gap-4 mb-8">
        <div class="min-w-0">
            <h1 class="font-serif text-3xl md:text-4xl text-zinc-900 dark:text-zinc-50 leading-tight tracking-tight">
                {{ __($greetingKey) }}{{ auth()->check() ? ', ' . explode(' ', auth()->user()->name)[0] : '' }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ now()->locale(app()->getLocale())->translatedFormat(app()->getLocale() === 'ar' ? 'l، j F' : 'l, F j') }}</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            @auth
                @if($streak > 0)
                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-600 dark:text-amber-400 whitespace-nowrap"><x-flame />{{ __(':count-day streak', ['count' => $streak]) }}</span>
                @endif
            @endauth
            @guest
                <a href="{{ route('register') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-600/20 whitespace-nowrap">
                    {{ __('Sign up to save') }}
                </a>
            @endguest
        </div>
    </div>

    {{-- ── Today progress ── --}}
    @auth
        @php
            $pct        = $dailyGoal > 0 ? min(100, round(($todayCalories / $dailyGoal) * 100)) : 0;
            $remaining  = $dailyGoal - $todayCalories;
            $barColor   = $pct >= 100 ? 'bg-rose-500' : ($pct >= 80 ? 'bg-amber-400 dark:bg-amber-500' : 'bg-emerald-500');
            $afterSaving = $remaining - $calories;
        @endphp
        <div class="pb-8 mb-10 border-b border-zinc-200/70 dark:border-zinc-800/80">
            <div class="flex items-baseline justify-between mb-2">
                <span class="text-xs font-semibold uppercase tracking-widest rtl:tracking-normal text-emerald-600 dark:text-emerald-400">{{ __('Eaten today') }}</span>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                    <span class="font-mono font-medium text-zinc-900 dark:text-zinc-100">{{ number_format($todayCalories) }}</span> / {{ number_format($dailyGoal) }}
                    <span class="mx-1 text-zinc-300 dark:text-zinc-600">·</span>
                    @if($remaining > 0)
                        <span class="text-emerald-600 dark:text-emerald-400">{{ __(':value left', ['value' => number_format($remaining)]) }}</span>
                    @elseif($remaining === 0)
                        <span class="text-emerald-600 dark:text-emerald-400">{{ __('goal reached') }}</span>
                    @else
                        <span class="text-rose-500 dark:text-rose-400">{{ __(':value over', ['value' => number_format(abs($remaining))]) }}</span>
                    @endif
                </span>
            </div>
            <div class="h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                <div class="h-full {{ $barColor }} rounded-full transition-all duration-700" style="width: {{ $pct }}%"></div>
            </div>
            @if($calories > 0)
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                    {{ __('Saving this puts you at') }}
                    <span class="font-mono font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($todayCalories + $calories) }}</span>
                    @if($afterSaving < 0)
                        <span class="text-rose-500 dark:text-rose-400">({{ __(':value over', ['value' => number_format(abs($afterSaving))]) }})</span>
                    @else
                        <span class="text-emerald-600 dark:text-emerald-400">({{ __(':value left', ['value' => number_format($afterSaving)]) }})</span>
                    @endif
                </p>
            @endif
        </div>
    @endauth

    {{-- ── Log a meal ── --}}
    <div class="mb-12">
        <div class="flex items-center justify-between gap-3 mb-3">
            <h2 class="font-serif text-2xl md:text-[1.75rem] text-zinc-900 dark:text-zinc-50 tracking-tight leading-tight">{{ __('Log a meal') }}</h2>
            @if(!$manualMode)
                <button wire:click="estimate"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        wire:target="estimate"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 text-white px-4 py-2 text-sm font-semibold shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 transition">
                    <span wire:loading wire:target="estimate" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <svg wire:loading.remove wire:target="estimate" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5z"/></svg>
                    {{ __('Estimate') }}
                </button>
            @endif
        </div>

        {{-- ── Manual entry: log calories directly ── --}}
        @if($manualMode)
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1.5">{{ __('Calories') }}</label>
                    <div class="relative">
                        <input type="number" min="1" wire:model="manualCalories" wire:keydown.enter="saveManual"
                               placeholder="{{ __('e.g. 650') }}"
                               class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 ps-4 pe-14 py-3 font-mono text-lg text-zinc-900 dark:text-zinc-100 placeholder:font-sans placeholder:text-base placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-400 dark:focus:border-emerald-500 focus:ring-2 focus:ring-emerald-400/30 focus:outline-none transition" />
                        <span class="absolute end-4 top-1/2 -translate-y-1/2 text-xs text-zinc-400 dark:text-zinc-500 pointer-events-none">{{ __('kcal') }}</span>
                    </div>
                    @error('manualCalories')
                        <p class="mt-1.5 text-xs text-rose-500 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <input type="text" wire:model="manualFood" maxlength="255"
                       placeholder="{{ __('What did you eat? (optional)') }}"
                       class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-2.5 text-sm text-zinc-800 dark:text-zinc-200 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-400 dark:focus:border-emerald-500 focus:ring-2 focus:ring-emerald-400/30 focus:outline-none transition" />

                <div>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-1.5">{{ __('Macros (optional)') }}</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach([['model' => 'manualProtein', 'letter' => __('P'), 'color' => 'text-rose-500 dark:text-rose-400'], ['model' => 'manualCarbs', 'letter' => __('C'), 'color' => 'text-indigo-500 dark:text-indigo-400'], ['model' => 'manualFat', 'letter' => __('F'), 'color' => 'text-amber-500 dark:text-amber-400']] as $m)
                            <div class="relative">
                                <span class="absolute start-3 top-1/2 -translate-y-1/2 text-xs font-bold {{ $m['color'] }} pointer-events-none">{{ $m['letter'] }}</span>
                                <input type="number" min="0" wire:model="{{ $m['model'] }}" placeholder="0"
                                       class="w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 ps-7 pe-6 py-2 font-mono text-sm text-zinc-800 dark:text-zinc-200 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-400 dark:focus:border-emerald-500 focus:ring-2 focus:ring-emerald-400/30 focus:outline-none transition" />
                                <span class="absolute end-3 top-1/2 -translate-y-1/2 text-xs text-zinc-400 dark:text-zinc-500 pointer-events-none">g</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <button wire:click="saveManual"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60 cursor-not-allowed"
                            wire:target="saveManual"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 text-white px-5 py-2.5 text-sm font-semibold shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 transition">
                        <span wire:loading wire:target="saveManual" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <svg wire:loading.remove wire:target="saveManual" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        {{ __('Add') }}
                    </button>
                    <button wire:click="toggleManual(false)" class="text-sm text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 transition">
                        ← {{ __('Describe your meal instead') }}
                    </button>
                </div>
            </div>
        @else

        <textarea wire:model="food" rows="3"
                  wire:keydown.meta.enter="estimate"
                  wire:keydown.ctrl.enter="estimate"
                  placeholder="{{ __('Describe what you ate — e.g. chicken shawarma bowl, large') }}"
                  class="w-full resize-none rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 text-sm text-zinc-800 dark:text-zinc-200 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-400 dark:focus:border-emerald-500 focus:ring-2 focus:ring-emerald-400/30 focus:outline-none transition"></textarea>
        @error('food')
            <p class="mt-1.5 text-xs text-rose-500 dark:text-rose-400">{{ $message }}</p>
        @enderror

        {{-- Estimate result --}}
        @if($food && $calories)
            <div wire:transition class="mt-4 rounded-xl border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/30 p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-widest rtl:tracking-normal text-emerald-600 dark:text-emerald-400 mb-1">{{ __('Estimated') }}</p>
                        <p class="font-mono text-3xl md:text-4xl font-bold text-zinc-900 dark:text-zinc-50 leading-none">
                            {{ number_format($calories) }}<span class="text-sm font-normal text-zinc-500 dark:text-zinc-400 ms-1.5">{{ __('kcal') }}</span>
                        </p>
                        @if($protein || $carbs || $fat)
                            <x-macros :protein="$protein" :carbs="$carbs" :fat="$fat" class="text-xs mt-3" />
                        @endif
                    </div>
                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60 cursor-not-allowed"
                            wire:target="save"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 text-white px-4 py-2 text-sm font-semibold shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 transition whitespace-nowrap">
                        <span wire:loading wire:target="save" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <svg wire:loading.remove wire:target="save" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ __('Save') }}
                    </button>
                </div>

                @if(!empty($breakdown))
                    <div class="mt-4 pt-3 border-t border-emerald-200/70 dark:border-emerald-800/40 flex flex-wrap gap-x-4 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                        @foreach($breakdown as $item)
                            <span><span class="font-mono text-zinc-700 dark:text-zinc-300">{{ number_format($item['kcal']) }}</span> {{ $item['text'] }}</span>
                        @endforeach
                    </div>
                @endif

                @if($explanation)
                    <div class="mt-3 flex items-start gap-2 text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="#059669" class="mt-0.5 shrink-0" aria-hidden="true"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5z"/></svg>
                        <span>{{ $explanation }}</span>
                    </div>
                @endif

                <div class="mt-4 pt-3 border-t border-emerald-200/70 dark:border-emerald-800/40">
                    @if($reusedManual)
                        {{-- Reused a past manual entry — let the user override with a fresh AI estimate (e.g. a different portion) --}}
                        <button wire:click="estimate(true)"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60 cursor-not-allowed"
                                wire:target="estimate"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 hover:text-emerald-800 dark:hover:text-emerald-200 transition">
                            <span wire:loading wire:target="estimate" class="inline-block w-3 h-3 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></span>
                            <svg wire:loading.remove wire:target="estimate" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5z"/></svg>
                            {{ __('Estimate with AI instead') }}
                        </button>
                    @else
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ __("Doesn't look right? Edit the description above and estimate again.") }}
                        </p>
                    @endif
                </div>
            </div>
        @endif

        @if(!$food)
            <p class="mt-3 text-xs text-zinc-400 dark:text-zinc-500">{{ __('Type what you ate in plain English and let AI do the counting.') }}</p>
        @endif

        <button wire:click="toggleManual(true)" class="mt-3 text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition">
            {{ __('Already know the calories? Add them manually') }} →
        </button>
        @endif
    </div>

    {{-- ── Today's meals ── --}}
    <div>
        <div class="flex items-baseline justify-between mb-5">
            <h2 class="font-serif text-2xl md:text-[1.75rem] text-zinc-900 dark:text-zinc-50 tracking-tight leading-tight">{{ __("Today's meals") }}</h2>
            @auth
                @if($todayEntries->isNotEmpty())
                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $todayEntries->count() }} {{ $todayEntries->count() === 1 ? __('entry') : __('entries') }}</span>
                @endif
            @endauth
        </div>

        @guest
            <div class="py-14 text-center">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Your meal history will appear here.') }}</p>
                <a href="{{ route('register') }}" class="inline-block mt-2 text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition">
                    {{ __('Create a free account to start tracking') }} →
                </a>
            </div>
        @endguest

        @auth
            @forelse($todayEntries as $entry)
                <div wire:key="{{ $entry->id }}" class="border-t border-zinc-100 dark:border-zinc-800/70 py-3 {{ $lastSavedId === $entry->id ? 'row-flash' : '' }}">
                    <x-entry-row :entry="$entry" :editing="$editingId === $entry->id" />
                </div>
            @empty
                <div wire:key="empty-state" class="py-14 text-center">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No meals logged yet today.') }}</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">{{ __('Log your first meal above to get started.') }}</p>
                </div>
            @endforelse

            <x-confirm-delete-modal :id="$confirmingDeleteId" :food="$confirmingDeleteFood" />
        @endauth
    </div>

</div>
