<x-app-layout>
    <div class="mx-auto max-w-5xl px-6 md:px-10 py-8 space-y-6">

        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-50">Profile</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Manage your account settings</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-sm">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>

    </div>
</x-app-layout>
