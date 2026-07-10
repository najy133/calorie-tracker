@props(['entry', 'editing' => false])

{{-- A single meal entry row: inline-edit form when editing, read-only otherwise.
     Shared by the Log page and the dashboard history (see HasEntryActions). --}}
@if($editing)
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
            <button wire:click="confirmDelete({{ $entry->id }})"
                    class="text-sm text-zinc-400 dark:text-zinc-500 hover:text-rose-500 dark:hover:text-rose-400 transition opacity-100 md:opacity-0 md:group-hover:opacity-100"
                    title="{{ __('Remove entry') }}">✕</button>
        </div>
    </div>
@endif
