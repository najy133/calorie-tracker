<div class="mx-auto max-w-5xl px-6 md:px-10 py-8">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-8 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-50">Your Progress</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">{{ now()->format('l, F j') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($streak > 0)
                <span class="px-3 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-sm font-medium">
                    🔥 {{ $streak }}-day streak
                </span>
            @endif
            <span class="px-3 py-1 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                Goal: {{ number_format($dailyGoal) }} kcal
            </span>
            <a href="{{ route('home') }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full bg-emerald-600 text-white text-sm font-medium shadow-sm hover:bg-emerald-700 transition">
                Log a Meal →
            </a>
        </div>
    </div>

    {{-- Weekly bar chart --}}
    <section class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm hover:shadow-md transition-shadow duration-200 mb-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Last 7 Days</h2>
            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                Avg: <span class="font-mono font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($weeklyAverage) }} kcal/day</span>
            </span>
        </div>

        {{-- Bars --}}
        <div class="flex items-end justify-between gap-2 h-36">
            @foreach($weeklyData as $index => $day)
                @php
                    $barColor = $day['pct'] >= 100
                        ? 'bg-rose-400 dark:bg-rose-500'
                        : ($day['pct'] >= 80 ? 'bg-amber-400 dark:bg-amber-500' : 'bg-emerald-400 dark:bg-emerald-500');
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1">
                    {{-- Calorie label --}}
                    <span class="font-mono text-xs text-zinc-500 dark:text-zinc-400 {{ $day['calories'] === 0 ? 'invisible' : '' }}">
                        {{ $day['calories'] >= 1000 ? number_format($day['calories'] / 1000, 1) . 'k' : $day['calories'] }}
                    </span>

                    {{-- Bar container --}}
                    <div class="w-full flex items-end h-24 {{ $day['isToday'] ? 'rounded-lg ring-2 ring-emerald-400 dark:ring-emerald-500 ring-offset-1 dark:ring-offset-zinc-900' : '' }}">
                        <div class="bar-animate w-full rounded-t-md {{ $barColor }} {{ $day['calories'] === 0 ? 'bg-zinc-100 dark:bg-zinc-800 rounded-md' : '' }}"
                             style="height: {{ max(4, $day['pct']) }}%; animation-delay: {{ $index * 0.06 }}s"></div>
                    </div>

                    {{-- Day label --}}
                    <span class="text-xs font-medium {{ $day['isToday'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                        {{ $day['isToday'] ? 'Today' : $day['label'] }}
                    </span>
                </div>
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="mt-4 flex items-center gap-4 text-xs text-zinc-400 dark:text-zinc-500">
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-emerald-400 dark:bg-emerald-500"></span> Under goal</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-amber-400 dark:bg-amber-500"></span> Near goal (≥80%)</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-rose-400 dark:bg-rose-500"></span> Over goal</span>
        </div>
    </section>

    {{-- History list --}}
    <section class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-50 mb-4">History</h2>

        @if($recentEntries->isEmpty())
            <div class="text-center py-12 text-zinc-400 dark:text-zinc-500">
                <div class="text-4xl mb-3">📋</div>
                <p class="text-sm">No meals logged yet.</p>
                <a href="{{ route('home') }}"
                   class="inline-block mt-3 text-sm text-emerald-600 dark:text-emerald-400 font-medium hover:underline">
                    Log your first meal
                </a>
            </div>
        @else
            <div class="space-y-6">
                @foreach($recentEntries as $dateString => $entries)
                    @php
                        $date      = \Carbon\Carbon::parse($dateString);
                        $dayLabel  = $date->isToday() ? 'Today' : ($date->isYesterday() ? 'Yesterday' : $date->format('l, M j'));
                        $dayTotal  = $entries->sum('calories');
                    @endphp

                    <div>
                        {{-- Day heading --}}
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">
                                {{ $dayLabel }}
                            </h3>
                            <span class="font-mono text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                {{ number_format($dayTotal) }} kcal
                            </span>
                        </div>

                        {{-- Entries --}}
                        <ul class="divide-y divide-zinc-100 dark:divide-zinc-800 rounded-xl border border-zinc-100 dark:border-zinc-800 overflow-hidden">
                            @foreach($entries as $entry)
                                <li wire:key="{{ $entry->id }}" class="px-4 py-3 bg-white dark:bg-zinc-900">
                                    @if($editingId === $entry->id)
                                        {{-- Inline edit form --}}
                                        <div class="space-y-2">
                                            <input wire:model="editFood" type="text"
                                                   class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 px-3 py-1.5 text-sm focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 focus:outline-none transition"
                                                   placeholder="Food description" />
                                            @error('editFood')
                                                <p class="text-xs text-rose-500 dark:text-rose-400">{{ $message }}</p>
                                            @enderror
                                            <p class="text-xs text-zinc-400 dark:text-zinc-500">Macros will be re-estimated automatically.</p>
                                            <div class="flex gap-2">
                                                <button wire:click="saveEdit"
                                                        wire:loading.attr="disabled"
                                                        wire:loading.class="opacity-60 cursor-not-allowed"
                                                        wire:target="saveEdit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition">
                                                    <span wire:loading wire:target="saveEdit" class="inline-block w-2.5 h-2.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                                    Save
                                                </button>
                                                <button wire:click="cancelEdit"
                                                        class="px-3 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-xs font-medium hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Normal row --}}
                                        <div class="flex items-center justify-between group">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate">{{ $entry->food }}</p>
                                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                                    {{ $entry->created_at->format('g:i A') }}
                                                    @if($entry->protein || $entry->carbs || $entry->fat)
                                                        &nbsp;·&nbsp;
                                                        <span class="text-indigo-500 dark:text-indigo-400">P{{ $entry->protein }}g</span>
                                                        &nbsp;<span class="text-amber-500 dark:text-amber-400">C{{ $entry->carbs }}g</span>
                                                        &nbsp;<span class="text-rose-400 dark:text-rose-400">F{{ $entry->fat }}g</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-3 ml-4 shrink-0">
                                                <span class="font-mono text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                                    {{ number_format($entry->calories) }} kcal
                                                </span>
                                                <button wire:click="startEdit({{ $entry->id }})"
                                                        class="text-xs text-zinc-300 dark:text-zinc-600 hover:text-indigo-500 dark:hover:text-indigo-400 transition opacity-0 group-hover:opacity-100"
                                                        title="Edit entry">✎</button>
                                                <button wire:click="delete({{ $entry->id }})"
                                                        wire:confirm="Remove this entry?"
                                                        class="text-xs text-zinc-300 dark:text-zinc-600 hover:text-rose-500 dark:hover:text-rose-400 transition opacity-0 group-hover:opacity-100"
                                                        title="Remove entry">✕</button>
                                            </div>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

</div>
