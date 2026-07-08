<nav x-data="{
        open: false,
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        }
    }" class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border-b border-zinc-200/60 dark:border-zinc-800/60 sticky top-0 z-40 transition-colors duration-200">
    <div class="max-w-5xl mx-auto px-6 md:px-10">
        <div class="flex items-center h-14 gap-4">

            {{-- Brand (left) --}}
            <div class="flex items-center flex-1 min-w-0">
                <a href="{{ auth()->check() ? route('dashboard') : route('landing') }}" class="text-base hover:opacity-80 transition">
                    <x-wordmark />
                </a>
            </div>

            {{-- Primary nav (centered, desktop, auth only) --}}
            @auth
                <div class="hidden sm:flex items-center gap-2">
                    <a href="{{ route('dashboard') }}"
                       class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('dashboard') ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100' }} transition">
                        {{ __('Dashboard') }}
                    </a>
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 transition">
                        {{ __('Log Meal') }}
                    </a>
                </div>
            @endauth

            {{-- Right side --}}
            <div class="flex items-center justify-end flex-1 gap-2">

                {{-- Desktop controls --}}
                <div class="hidden sm:flex items-center gap-2">

                    {{-- Dark mode toggle --}}
                    <button @click="toggle()"
                            class="p-1.5 rounded-lg text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                            :title="dark ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}'">
                        <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14A7 7 0 0012 5z"/>
                        </svg>
                        <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>

                    @auth
                        @php
                            $navUser    = auth()->user();
                            $navInitial = mb_strtoupper(mb_substr($navUser->name ?? '?', 0, 1));
                        @endphp

                        {{-- Account menu --}}
                        <div x-data="{ userMenu: false }" class="relative">
                            <button @click="userMenu = !userMenu"
                                    class="w-8 h-8 rounded-full bg-emerald-600 text-white font-serif text-sm flex items-center justify-center hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 transition"
                                    :aria-expanded="userMenu" aria-haspopup="true" aria-label="{{ __('Account menu') }}">
                                {{ $navInitial }}
                            </button>

                            <div x-show="userMenu" x-cloak @click.outside="userMenu = false" @keydown.escape.window="userMenu = false"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                                 class="absolute end-0 mt-2 w-56 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-lg shadow-zinc-900/5 py-1.5 z-50">

                                <div class="px-3.5 py-2.5 border-b border-zinc-100 dark:border-zinc-800">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-50 truncate">{{ $navUser->name }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $navUser->email }}</p>
                                </div>

                                <a href="{{ route('profile.edit') }}"
                                   class="flex items-center gap-2.5 px-3.5 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                    <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    {{ __('Profile') }}
                                </a>

                                <form method="POST" action="{{ route('locale.switch') }}">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                                    <button type="submit"
                                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-sm text-start text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                        <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                        {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}
                                    </button>
                                </form>

                                <div class="my-1 border-t border-zinc-100 dark:border-zinc-800"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-sm text-start text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                        {{ __('Log out') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        {{-- Language toggle (guest) --}}
                        <form method="POST" action="{{ route('locale.switch') }}">
                            @csrf
                            <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                            <button type="submit"
                                    class="px-2.5 py-1 rounded-md text-xs font-semibold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition tracking-wide">
                                {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                            </button>
                        </form>

                        <a href="{{ route('login') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm font-medium text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-600 transition">
                            {{ __('Log in') }}
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 transition">
                            {{ __('Sign up free') }}
                        </a>
                    @endauth
                </div>

                {{-- Mobile: language + theme + hamburger --}}
                <div class="flex items-center gap-1 sm:hidden">
                    <form method="POST" action="{{ route('locale.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                        <button type="submit"
                                class="px-2 py-1.5 rounded-md text-xs font-semibold text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                            {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                        </button>
                    </form>

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
    </div>

    {{-- Mobile menu --}}
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-zinc-200/60 dark:border-zinc-800/60">
        <div class="px-6 py-4 space-y-3">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="block text-sm {{ request()->routeIs('dashboard') ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100' }} transition">
                    {{ __('Dashboard') }}
                </a>

                <a href="{{ route('home') }}"
                   class="block text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 transition">
                    {{ __('Log Meal') }}
                </a>

                <a href="{{ route('profile.edit') }}"
                   class="block text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition">
                    {{ __('Profile') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-rose-600 dark:text-rose-400 hover:text-rose-700 transition">
                        {{ __('Log out') }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="block text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition">
                    {{ __('Log in') }}
                </a>
                <a href="{{ route('register') }}"
                   class="block text-sm text-emerald-600 dark:text-emerald-400 font-medium hover:text-emerald-700 transition">
                    {{ __('Sign up free') }}
                </a>
            @endauth
        </div>
    </div>
</nav>
