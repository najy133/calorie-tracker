<div class="mx-auto max-w-5xl px-6 md:px-10 py-8">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-8 flex-wrap gap-3">
        <div>
            @php
                $hour = now()->hour;
                $greeting = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening');
            @endphp
            <h1 class="text-2xl font-bold text-gray-900">
                Good {{ $greeting }}{{ auth()->check() ? ', ' . auth()->user()->name : '' }}!
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ now()->format('l, F j') }}</p>
        </div>
        @guest
            <a href="{{ route('register') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium shadow-sm hover:opacity-90 transition">
                Sign up to save
            </a>
        @endguest
    </div>

    {{-- Main grid --}}
    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Today's Calories Card --}}
        <section class="relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-indigo-400/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 rounded-full bg-purple-400/20 blur-2xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Today's Calories</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                        Goal: 2,000 kcal
                    </span>
                </div>

                <div class="mb-6">
                    <div class="flex items-baseline gap-2">
                        <span class="text-5xl font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 bg-clip-text text-transparent">
                            {{ number_format($todayCalories) }}
                        </span>
                        <span class="text-lg text-gray-500 font-medium">kcal</span>
                    </div>
                    @if($todayCalories === 0)
                        <p class="text-sm text-gray-400 mt-1">
                            @auth Start logging to track your day @else Estimate a meal below — sign up to save @endauth
                        </p>
                    @endif
                </div>

                @php
                    $pct = $dailyGoal > 0 ? min(100, round(($todayCalories / $dailyGoal) * 100)) : 0;
                    $barColor = $pct >= 100
                        ? 'from-red-500 to-red-600'
                        : ($pct >= 80 ? 'from-amber-400 to-orange-500' : 'from-indigo-500 to-purple-500');
                    $remaining = $dailyGoal - $todayCalories;
                @endphp

                <div class="mb-2">
                    <div class="h-3 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r {{ $barColor }} transition-all duration-500"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-gray-500">
                        <span>{{ number_format($todayCalories) }} kcal eaten</span>
                        <span>{{ number_format($dailyGoal) }} kcal goal</span>
                    </div>
                </div>

                @auth
                    <p class="text-center text-sm mb-4 font-medium {{ $remaining < 0 ? 'text-red-600' : 'text-gray-400' }}">
                        @if($remaining > 0)
                            {{ number_format($remaining) }} kcal remaining
                        @elseif($remaining === 0)
                            Daily goal reached!
                        @else
                            {{ number_format(abs($remaining)) }} kcal over goal
                        @endif
                    </p>
                @endauth

                @if($explanation)
                    <div wire:transition class="flex items-start gap-2 p-3 rounded-xl bg-gray-50 border border-gray-200">
                        <span class="text-indigo-500 mt-0.5 shrink-0">✨</span>
                        <p class="text-sm text-gray-600">{{ $explanation }}</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Log Meal Card --}}
        <section class="relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="absolute -top-16 -right-16 w-32 h-32 rounded-full bg-emerald-400/10 blur-2xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Log Meal</h2>
                    <button
                        wire:click="estimate"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        wire:target="estimate"
                        class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 py-2 text-sm font-medium shadow hover:opacity-90 transition"
                    >
                        <span wire:loading wire:target="estimate" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span wire:loading.remove wire:target="estimate">⚡</span>
                        Estimate
                    </button>
                </div>

                <textarea
                    wire:model="food"
                    rows="4"
                    placeholder="🍽️ Describe what you ate...

Example: 2 eggs, toast with butter"
                    class="w-full min-h-[140px] resize-none rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-700 placeholder:text-gray-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/40 focus:outline-none focus:bg-white transition"
                ></textarea>
                @error('food')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror

                @if($food && $calories)
                    <div wire:transition class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-emerald-700 uppercase tracking-wide font-medium">Estimated</p>
                                <p class="text-2xl font-bold text-emerald-700">
                                    {{ number_format($calories) }} <span class="text-sm font-medium">kcal</span>
                                </p>
                                @if($protein || $carbs || $fat)
                                    <p class="text-xs text-emerald-600 mt-1">
                                        P: {{ $protein }}g &nbsp;·&nbsp; C: {{ $carbs }}g &nbsp;·&nbsp; F: {{ $fat }}g
                                    </p>
                                @endif
                            </div>
                            <button
                                wire:click="save"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60 cursor-not-allowed"
                                wire:target="save"
                                class="inline-flex items-center gap-2 rounded-full bg-emerald-600 text-white px-4 py-2 text-sm font-medium shadow hover:opacity-90 transition"
                            >
                                <span wire:loading wire:target="save" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span wire:loading.remove wire:target="save">✔</span>
                                Save
                            </button>
                        </div>
                    </div>
                @endif

                @if(!$food)
                    <div class="mt-6 text-center text-gray-400">
                        <div class="text-3xl mb-2">🥗 🍳 🥤</div>
                        <p class="text-sm">Start typing to log your meal</p>
                    </div>
                @endif
            </div>
        </section>

    </div>

    {{-- Meal History --}}
    <section class="mt-6 rounded-2xl bg-white/80 backdrop-blur border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Today's Meals</h2>
            @if($todayEntries->isNotEmpty())
                <span class="text-xs text-gray-400">
                    {{ $todayEntries->count() }} {{ $todayEntries->count() === 1 ? 'entry' : 'entries' }}
                </span>
            @endif
        </div>

        @guest
            <div class="text-center py-10 text-gray-400">
                <div class="text-4xl mb-3">🔒</div>
                <p class="text-sm">Your meal history will appear here.</p>
                <a href="{{ route('register') }}"
                   class="inline-block mt-3 text-sm text-indigo-600 font-medium hover:underline">
                    Create a free account to start tracking
                </a>
            </div>
        @endguest

        @auth
            @if($todayEntries->isEmpty())
                <div class="text-center py-10 text-gray-400">
                    <div class="text-4xl mb-3">🍽️</div>
                    <p class="text-sm">No meals logged yet today.</p>
                    <p class="text-xs mt-1">Log your first meal above to get started.</p>
                </div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($todayEntries as $entry)
                        <li wire:key="{{ $entry->id }}" class="py-3">
                            @if($editingId === $entry->id)
                                {{-- Inline edit form --}}
                                <div class="space-y-2">
                                    <input wire:model="editFood" type="text"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/40 focus:outline-none"
                                           placeholder="Food description" />
                                    @error('editFood')
                                        <p class="text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-400">Macros will be re-estimated automatically.</p>
                                    <div class="flex gap-2">
                                        <button wire:click="saveEdit"
                                                wire:loading.attr="disabled"
                                                wire:loading.class="opacity-60 cursor-not-allowed"
                                                wire:target="saveEdit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:opacity-90 transition">
                                            <span wire:loading wire:target="saveEdit" class="inline-block w-2.5 h-2.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
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
            @endif
        @endauth
    </section>

</div>
