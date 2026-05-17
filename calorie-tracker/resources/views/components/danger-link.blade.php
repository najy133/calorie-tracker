{{--
    <x-danger-link>Delete account</x-danger-link>
    --------------------------------------------------------------
    Soft-tone danger button — looks like a secondary button but
    with rose text. Use as the FIRST click that opens a confirm
    modal (low stakes). The solid <x-danger-button> is reserved
    for inside the modal (high stakes, primary destructive action).
--}}
<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg font-medium text-sm text-rose-600 dark:text-rose-400 shadow-sm hover:bg-rose-50 dark:hover:bg-rose-950/30 hover:border-rose-200 dark:hover:border-rose-900 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
]) }}>
    {{ $slot }}
</button>
