@props(['id' => null, 'food' => ''])

{{-- Confirm-delete modal for a meal entry. Visibility is pure Livewire state
     ($id) so DOM morphs can't desync it the way an Alpine x-show binding would.
     Backed by confirmDelete/deleteConfirmed in HasEntryActions. --}}
@if($id !== null)
    <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-start justify-center"
         x-data x-on:keydown.escape.window="$wire.set('confirmingDeleteId', null)">
        <div class="fixed inset-0 bg-zinc-900/50" wire:click="$set('confirmingDeleteId', null)"></div>
        <div class="step-in relative z-10 mt-24 w-full sm:max-w-md rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xl p-6">
            <h2 class="font-serif text-2xl text-zinc-900 dark:text-zinc-50">{{ __('Remove this entry?') }}</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                <span class="font-medium text-zinc-900 dark:text-zinc-100">“{{ $food }}”</span>
                {{ __('and its calories will be removed from your log. There is no undo.') }}
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button wire:click="$set('confirmingDeleteId', null)">{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button wire:click="deleteConfirmed" wire:loading.attr="disabled" wire:target="deleteConfirmed">{{ __('Remove entry') }}</x-danger-button>
            </div>
        </div>
    </div>
@endif
