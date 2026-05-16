<nav x-data="{
        open: false,
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        }
    }" class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border-b border-zinc-200/60 dark:border-zinc-800/60 sticky top-0 z-20 transition-colors duration-200">
    <div class="max-w-5xl mx-auto px-6 md:px-10">
        <div class="flex justify-between h-14">

            {{-- Brand --}}
            <div class="flex items-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-base font-semibold text-zinc-900 dark:text-zinc-50 tracking-tight hover:text-emerald-600 dark:hover:text-emerald-400 transition">
                        Calorie Tracker
                    </a>
                @else
                    <a href="{{ route('home') }}" class="text-base font-semibold text-zinc-900 dark:text-zinc-50 tracking-tight hover:text-emerald-600 dark:hover:text-emerald-400 transition">
                        Calorie Tracker
                    </a>
                @endauth
            </div>

            {{-- Desktop right side --}}
            <div class="hidden sm:flex sm:items-center gap-3">

                {{-- Dark mode toggle --}}
                <button @click="toggle()"
                        class="p-1.5 rounded-lg text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                        :title="dark ? 'Switch to light mode' : 'Switch to dark mode'">
                    <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14A7 7 0 0012 5z"/>
                    </svg>
                    <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                @auth
                    <a href="{{ route('dashboard') }}"
                       class="text-sm {{ request()->routeIs('dashboard') ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100' }} transition">
                        Dashboard
                    </a>

                    <a href="{{ route('home') }}"
                       class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-emerald-600 text-white text-sm font-medium shadow-sm hover:bg-emerald-700 transition">
                        Log Meal
                    </a>

                    <a href="{{ route('profile.edit') }}"
                       class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition">
                        Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-rose-500 dark:hover:text-rose-400 transition">
                            Log out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition">
                        Log in
                    </a>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-emerald-600 text-white text-sm font-medium shadow-sm hover:bg-emerald-700 transition">
                        Sign up free
                    </a>
                @endauth
            </div>

            {{-- Mobile: toggle + hamburger --}}
            <div class="-me-2 flex items-center gap-1 sm:hidden">
                <button @click="toggle()"
                        class="p-2 rounded-md text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                    <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14A7 7 0 0012 5z"/>
                    </svg>
                    <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                <button @click="open = !open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-zinc-200/60 dark:border-zinc-800/60">
        <div class="px-6 py-4 space-y-3">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="block text-sm {{ request()->routeIs('dashboard') ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100' }} transition">
                    Dashboard
                </a>

                <a href="{{ route('home') }}"
                   class="block text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 transition">
                    Log Meal
                </a>

                <a href="{{ route('profile.edit') }}"
                   class="block text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition">
                    Profile
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-rose-500 dark:hover:text-rose-400 transition">
                        Log out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="block text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition">
                    Log in
                </a>
                <a href="{{ route('register') }}"
                   class="block text-sm text-emerald-600 dark:text-emerald-400 font-medium hover:text-emerald-700 transition">
                    Sign up free
                </a>
            @endauth
        </div>
    </div>
</nav>
