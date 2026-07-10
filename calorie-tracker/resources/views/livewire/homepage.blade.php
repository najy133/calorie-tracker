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
            <button wire:click="estimate"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-not-allowed"
                    wire:target="estimate"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 text-white px-4 py-2 text-sm font-semibold shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 transition">
                <span wire:loading wire:target="estimate" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <svg wire:loading.remove wire:target="estimate" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5z"/></svg>
                {{ __('Estimate') }}
            </button>
        </div>

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

                <p class="mt-4 pt-3 border-t border-emerald-200/70 dark:border-emerald-800/40 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __("Doesn't look right? Edit the description above and estimate again.") }}
                </p>
            </div>
        @endif

        @if(!$food)
            <p class="mt-3 text-xs text-zinc-400 dark:text-zinc-500">{{ __('Type what you ate in plain English and let AI do the counting.') }}</p>
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
                    @if($editingId === $entry->id)
                        <div class="space-y-2">
                            <input wire:model="editFood" type="text"
                                   class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 px-3 py-1.5 text-sm focus:border-emerald-400 dark:focus:border-emerald-500 focus:ring-2 focus:ring-emerald-400/40 focus:outline-none transition"
                                   placeholder="{{ __('Food description') }}" />
                            @error('editFood')
                                <p class="text-xs text-rose-500 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Macros will be re-estimated automatically.') }}</p>
                            <div class="flex gap-2">
                                <button wire:click="saveEdit"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-60 cursor-not-allowed"
                                        wire:target="saveEdit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-600 text-white text-xs font-medium hover:bg-emerald-700 transition">
                                    <span wire:loading wire:target="saveEdit" class="inline-block w-2.5 h-2.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    {{ __('Save') }}
                                </button>
                                <button wire:click="cancelEdit"
                                        class="px-3 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-xs font-medium hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center justify-between group gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate">{{ $entry->food }}</p>
                                <div class="flex items-center gap-2.5 mt-1">
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500 whitespace-nowrap">{{ $entry->created_at->format('g:i') }} {{ __($entry->created_at->format('A')) }}</span>
                                    @if($entry->protein || $entry->carbs || $entry->fat)
                                        <x-macros :protein="$entry->protein" :carbs="$entry->carbs" :fat="$entry->fat" class="text-xs" />
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="font-mono text-sm font-semibold text-zinc-700 dark:text-zinc-300 whitespace-nowrap">
                                    {{ number_format($entry->calories) }} {{ __('kcal') }}
                                </span>
                                <button wire:click="startEdit({{ $entry->id }})"
                                        class="text-sm text-zinc-400 dark:text-zinc-500 hover:text-emerald-500 dark:hover:text-emerald-400 transition opacity-100 md:opacity-0 md:group-hover:opacity-100"
                                        title="{{ __('Edit entry') }}">✎</button>
                                <button x-data x-on:click="$dispatch('open-modal', 'confirm-delete-entry')"
                                        wire:click="confirmDelete({{ $entry->id }})"
                                        class="text-sm text-zinc-400 dark:text-zinc-500 hover:text-rose-500 dark:hover:text-rose-400 transition opacity-100 md:opacity-0 md:group-hover:opacity-100"
                                        title="{{ __('Remove entry') }}">✕</button>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div wire:key="empty-state" class="py-14 text-center">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No meals logged yet today.') }}</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">{{ __('Log your first meal above to get started.') }}</p>
                </div>
            @endforelse

            {{-- Confirm-delete modal --}}
            <x-modal name="confirm-delete-entry" maxWidth="md" focusable>
                <div class="p-6">
                    <h2 class="font-serif text-2xl text-zinc-900 dark:text-zinc-50">{{ __('Remove this entry?') }}</h2>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">“{{ $confirmingDeleteFood }}”</span>
                        {{ __('and its calories will be removed from your log. There is no undo.') }}
                    </p>
                    <div class="mt-6 flex justify-end gap-3">
                        <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                        <x-danger-button x-on:click="$dispatch('close')" wire:click="deleteConfirmed">{{ __('Remove entry') }}</x-danger-button>
                    </div>
                </div>
            </x-modal>
        @endauth
    </div>

</div>
