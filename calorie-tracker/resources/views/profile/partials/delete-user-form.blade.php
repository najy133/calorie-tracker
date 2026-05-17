<section class="flex items-start justify-between gap-4 flex-wrap">
    <div class="flex-1 min-w-0">
        <h2 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Delete account</h2>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400 max-w-md">
            Once deleted, all your entries, your streak, and your goal will be permanently removed. There is no undo.
        </p>
    </div>

    <x-danger-link
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="shrink-0"
    >Delete account</x-danger-link>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-50">
                Are you sure you want to delete your account?
            </h2>

            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                Once deleted, all of your data is permanently removed. Enter your password to confirm.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Password" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="block w-full"
                    placeholder="Password"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                <x-danger-button>Delete account</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
