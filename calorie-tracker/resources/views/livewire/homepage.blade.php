<div class="mx-auto max-w-5xl px-6 md:px-10 py-8">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-8 flex-wrap gap-3">
        <div>
            @php
                $hour = now()->hour;
                $greeting = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening');
            @endphp
            <h1 class="font-serif text-3xl text-zinc-900 dark:text-zinc-50">
                Good {{ $greeting }}{{ auth()->check() ? ', ' . auth()->user()->name : '' }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">{{ now()->format('l, F j') }}</p>
        </div>
        @guest
            <a href="{{ route('register') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-600 text-white text-sm font-medium shadow-sm hover:bg-emerald-700 transition">
                Sign up to save
            </a>
        @endguest
    </div>

    {{-- Main grid --}}
    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Today's Calories Card --}}
        <section class="relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Today's Calories</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                        Goal: {{ number_format($dailyGoal) }} kcal
                    </span>
                </div>

                {{-- Circular progress ring --}}
                @php
                    $pct          = $dailyGoal > 0 ? min(100, round(($todayCalories / $dailyGoal) * 100)) : 0;
                    $r            = 50;
                    $circumf      = 2 * M_PI * $r;
                    $dashoffset   = $circumf * (1 - $pct / 100);
                    $remaining    = $dailyGoal - $todayCalories;
                    $ringColor    = $pct >= 100 ? '#f43f5e' : ($pct >= 80 ? '#f59e0b' : '#10b981');
                    $trackColor   = 'text-zinc-100 dark:text-zinc-800';
                @endphp

                <div class="flex flex-col items-center my-2">
                    <div class="relative w-44 h-44">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                            {{-- Track --}}
                            <circle cx="60" cy="60" r="{{ $r }}" fill="none"
                                    stroke="currentColor"
                                    class="{{ $trackColor }}"
                                    stroke-width="10" />
                            {{-- Progress --}}
                            <circle cx="60" cy="60" r="{{ $r }}" fill="none"
                                    stroke="{{ $ringColor }}"
                                    stroke-width="10"
                                    stroke-dasharray="{{ number_format($circumf, 2) }}"
                                    stroke-dashoffset="{{ number_format($dashoffset, 2) }}"
                                    stroke-linecap="round"
                                    class="transition-all duration-700" />
                        </svg>
                        {{-- Center label --}}
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-mono text-3xl font-bold text-zinc-900 dark:text-zinc-50 leading-none">
                                {{ number_format($todayCalories) }}
                            </span>
                            <span class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">kcal eaten</span>
                        </div>
                    </div>

                    @auth
                        <p class="text-sm font-medium mt-3 {{ $remaining < 0 ? 'text-rose-500 dark:text-rose-400' : ($remaining === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400') }}">
                            @if($remaining > 0)
                                {{ number_format($remaining) }} kcal remaining
                            @elseif($remaining === 0)
                                Daily goal reached!
                            @else
                                {{ number_format(abs($remaining)) }} kcal over goal
                            @endif
                        </p>
                    @else
                        <p class="text-sm text-zinc-400 dark:text-zinc-500 mt-3">
                            Estimate a meal below — sign up to save
                        </p>
                    @endauth
                </div>

                @if($explanation)
                    <div wire:transition class="flex items-start gap-2 p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 mt-3">
                        <span class="text-emerald-500 mt-0.5 shrink-0">✨</span>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $explanation }}</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Log Meal Card --}}
        <section class="relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Log Meal</h2>
                    <button
                        wire:click="estimate"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        wire:target="estimate"
                        class="inline-flex items-center gap-2 rounded-full bg-indigo-600 dark:bg-indigo-500 text-white px-4 py-2 text-sm font-medium shadow hover:bg-indigo-700 dark:hover:bg-indigo-600 transition"
                    >
                        <span wire:loading wire:target="estimate" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span wire:loading.remove wire:target="estimate">⚡</span>
                        Estimate
                    </button>
                </div>

                <textarea
                    wire:model="food"
                    rows="4"
                    placeholder="Describe what you ate...

Example: 2 eggs, toast with butter"
                    class="w-full min-h-[140px] resize-none rounded-xl border-2 border-dashed border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 p-4 text-sm text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400 dark:placeholder:text-zinc-600 focus:border-indigo-400 dark:focus:border-indigo-500 focus:ring-2 focus:ring-indigo-400/40 focus:outline-none focus:bg-white dark:focus:bg-zinc-800 transition"
                ></textarea>
                @error('food')
                    <p class="mt-1 text-xs text-rose-500 dark:text-rose-400">{{ $message }}</p>
                @enderror

                @if($food && $calories)
                    <div wire:transition class="mt-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-xs text-emerald-700 dark:text-emerald-400 uppercase tracking-wide font-medium mb-1">Estimated</p>
                                <p class="font-mono text-2xl font-semibold text-emerald-700 dark:text-emerald-400">
                                    {{ number_format($calories) }} <span class="text-sm font-medium">kcal</span>
                                </p>

                                {{-- Macro bars --}}
                                @if($protein || $carbs || $fat)
                                    @php $macroTotal = $protein + $carbs + $fat; @endphp
                                    <div class="mt-3 space-y-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400 w-5">P</span>
                                            <div class="flex-1 h-1.5 bg-emerald-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-indigo-500 rounded-full transition-all duration-500"
                                                     style="width: {{ $macroTotal > 0 ? round($protein / $macroTotal * 100) : 0 }}%"></div>
                                            </div>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400 w-8 text-right">{{ $protein }}g</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-amber-600 dark:text-amber-400 w-5">C</span>
                                            <div class="flex-1 h-1.5 bg-emerald-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-amber-400 rounded-full transition-all duration-500"
                                                     style="width: {{ $macroTotal > 0 ? round($carbs / $macroTotal * 100) : 0 }}%"></div>
                                            </div>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400 w-8 text-right">{{ $carbs }}g</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-rose-500 dark:text-rose-400 w-5">F</span>
                                            <div class="flex-1 h-1.5 bg-emerald-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-rose-400 rounded-full transition-all duration-500"
                                                     style="width: {{ $macroTotal > 0 ? round($fat / $macroTotal * 100) : 0 }}%"></div>
                                            </div>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400 w-8 text-right">{{ $fat }}g</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <button
                                wire:click="save"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60 cursor-not-allowed"
                                wire:target="save"
                                class="shrink-0 inline-flex items-center gap-2 rounded-full bg-emerald-600 text-white px-4 py-2 text-sm font-medium shadow hover:bg-emerald-700 transition"
                            >
                                <span wire:loading wire:target="save" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span wire:loading.remove wire:target="save">✔</span>
                                Save
                            </button>
                        </div>
                    </div>
                @endif

                @if(!$food)
                    <div class="mt-6 text-center text-zinc-400 dark:text-zinc-600">
                        <div class="text-3xl mb-2">🥗 🍳 🥤</div>
                        <p class="text-sm">Start typing to log your meal</p>
                    </div>
                @endif
            </div>
        </section>

    </div>

    {{-- Meal History --}}
    <section class="mt-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Today's Meals</h2>
            @if($todayEntries->isNotEmpty())
                <span class="text-xs text-zinc-400 dark:text-zinc-500">
                    {{ $todayEntries->count() }} {{ $todayEntries->count() === 1 ? 'entry' : 'entries' }}
                </span>
            @endif
        </div>

        @guest
            <div class="text-center py-10 text-zinc-400 dark:text-zinc-500">
                <div class="text-4xl mb-3">🔒</div>
                <p class="text-sm">Your meal history will appear here.</p>
                <a href="{{ route('register') }}"
                   class="inline-block mt-3 text-sm text-emerald-600 dark:text-emerald-400 font-medium hover:underline">
                    Create a free account to start tracking
                </a>
            </div>
        @endguest

        @auth
            @if($todayEntries->isEmpty())
                <div class="text-center py-10 text-zinc-400 dark:text-zinc-500">
                    <div class="text-4xl mb-3">🍽️</div>
                    <p class="text-sm">No meals logged yet today.</p>
                    <p class="text-xs mt-1">Log your first meal above to get started.</p>
                </div>
            @else
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($todayEntries as $entry)
                        <li wire:key="{{ $entry->id }}" class="py-3">
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
            @endif
        @endauth
    </section>

</div>
