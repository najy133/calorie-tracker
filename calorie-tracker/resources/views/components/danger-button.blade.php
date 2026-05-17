<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center px-4 py-2 bg-rose-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150'
]) }}>
    {{ $slot }}
</button>
