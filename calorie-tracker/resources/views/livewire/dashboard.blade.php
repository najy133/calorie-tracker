<div class="mx-auto max-w-5xl px-6 md:px-10 py-8">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-8 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Your Progress</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ now()->format('l, F j') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($streak > 0)
                <span class="px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 text-sm font-semibold">
                    🔥 {{ $streak }}-day streak
                </span>
            @endif
            <span class="px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                Goal: {{ number_format($dailyGoal) }} kcal
            </span>
            <a href="{{ route('home') }}"
               class="inline-flex items-center px-4 py-1.5 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium shadow-sm hover:opacity-90 transition">
                Log a Meal →
            </a>
        </div>
    </div>

    {{-- Weekly bar chart --}}
    <section class="rounded-2xl bg-white/80 backdrop-blur border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200 mb-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Last 7 Days</h2>
            <span class="text-sm text-gray-500">
                Avg: <span class="font-medium text-gray-700">{{ number_format($weeklyAverage) }} kcal/day</span>
            </span>
        </div>

        {{-- Bars --}}
        <div class="flex items-end justify-between gap-2 h-36">
            @foreach($weeklyData as $day)
                @php
                    $barColor = $day['pct'] >= 100
                        ? 'bg-red-400'
                        : ($day['pct'] >= 80 ? 'bg-amber-400' : 'bg-indigo-400');
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1">
                    {{-- Calorie label --}}
                    <span class="text-xs text-gray-500 {{ $day['calories'] === 0 ? 'invisible' : '' }}">
                        {{ $day['calories'] >= 1000 ? number_format($day['calories'] / 1000, 1) . 'k' : $day['calories'] }}
                    </span>

                    {{-- Bar container --}}
                    <div class="w-full flex items-end h-24 {{ $day['isToday'] ? 'rounded-lg ring-2 ring-indigo-300 ring-offset-1' : '' }}">
                        <div class="w-full rounded-t-md {{ $barColor }} transition-all duration-500 {{ $day['calories'] === 0 ? 'bg-gray-100 rounded-md' : '' }}"
                             style="height: {{ max(4, $day['pct']) }}%"></div>
                    </div>

                    {{-- Day label --}}
                    <span class="text-xs font-medium {{ $day['isToday'] ? 'text-indigo-600' : 'text-gray-400' }}">
                        {{ $day['isToday'] ? 'Today' : $day['label'] }}
                    </span>
                </div>
            @endforeach
        </div>

        {{-- Goal line legend --}}
        <div class="mt-4 flex items-center gap-4 text-xs text-gray-400">
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-indigo-400"></span> Under goal</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-amber-400"></span> Near goal (≥80%)</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-red-400"></span> Over goal</span>
        </div>
    </section>

    {{-- History list --}}
    <section class="rounded-2xl bg-white/80 backdrop-blur border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">History</h2>

        @if($recentEntries->isEmpty())
            <div class="text-center py-12 text-gray-400">
                <div class="text-4xl mb-3">📋</div>
                <p class="text-sm">No meals logged yet.</p>
                <a href="{{ route('home') }}"
                   class="inline-block mt-3 text-sm text-indigo-600 font-medium hover:underline">
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
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                {{ $dayLabel }}
                            </h3>
                            <span class="text-xs font-medium text-gray-500">
                                {{ number_format($dayTotal) }} kcal
                            </span>
                        </div>

                        {{-- Entries --}}
                        <ul class="divide-y divide-gray-100 rounded-xl border border-gray-100 overflow-hidden">
                            @foreach($entries as $entry)
                                <li wire:key="{{ $entry->id }}" class="px-4 py-3 bg-white">
                                    @if($editingId === $entry->id)
                                        {{-- Inline edit form --}}
                                        <div class="space-y-2">
                                            <input wire:model="editFood" type="text"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/40 focus:outline-none"
                                                   placeholder="Food description" />
                                            <div class="flex gap-2 flex-wrap">
                                                <div class="flex items-center gap-1">
                                                    <label class="text-xs text-gray-500">kcal</label>
                                                    <input wire:model="editCalories" type="number" min="1"
                                                           class="w-20 rounded-lg border border-gray-300 px-2 py-1 text-sm focus:border-indigo-400 focus:outline-none" />
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <label class="text-xs text-gray-500">P</label>
                                                    <input wire:model="editProtein" type="number" min="0"
                                                           class="w-16 rounded-lg border border-gray-300 px-2 py-1 text-sm focus:border-indigo-400 focus:outline-none" />
                                                    <span class="text-xs text-gray-400">g</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <label class="text-xs text-gray-500">C</label>
                                                    <input wire:model="editCarbs" type="number" min="0"
                                                           class="w-16 rounded-lg border border-gray-300 px-2 py-1 text-sm focus:border-indigo-400 focus:outline-none" />
                                                    <span class="text-xs text-gray-400">g</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <label class="text-xs text-gray-500">F</label>
                                                    <input wire:model="editFat" type="number" min="0"
                                                           class="w-16 rounded-lg border border-gray-300 px-2 py-1 text-sm focus:border-indigo-400 focus:outline-none" />
                                                    <span class="text-xs text-gray-400">g</span>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <button wire:click="saveEdit"
                                                        class="px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:opacity-90 transition">
                                                    Save
                                                </button>
                                                <button wire:click="cancelEdit"
                                                        class="px-3 py-1 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Normal row --}}
                                        <div class="flex items-center justify-between group">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-800 truncate">{{ $entry->food }}</p>
                                                <p class="text-xs text-gray-400 mt-0.5">
                                                    {{ $entry->created_at->format('g:i A') }}
                                                    @if($entry->protein || $entry->carbs || $entry->fat)
                                                        &nbsp;·&nbsp; P: {{ $entry->protein }}g &nbsp;C: {{ $entry->carbs }}g &nbsp;F: {{ $entry->fat }}g
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-3 ml-4 shrink-0">
                                                <span class="text-sm font-semibold text-indigo-700">
                                                    {{ number_format($entry->calories) }} kcal
                                                </span>
                                                <button wire:click="startEdit({{ $entry->id }})"
                                                        class="text-xs text-gray-300 hover:text-indigo-500 transition opacity-0 group-hover:opacity-100"
                                                        title="Edit entry">✎</button>
                                                <button wire:click="delete({{ $entry->id }})"
                                                        wire:confirm="Remove this entry?"
                                                        class="text-xs text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100"
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
