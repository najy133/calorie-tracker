<nav x-data="{ open: false }" class="bg-white/70 backdrop-blur-md border-b border-gray-200/60 sticky top-0 z-20">
    <div class="max-w-5xl mx-auto px-6 md:px-10">
        <div class="flex justify-between h-14">

            {{-- Brand --}}
            <div class="flex items-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-lg font-bold text-gray-900 tracking-tight hover:text-indigo-600 transition">
                        Calorie Tracker
                    </a>
                @else
                    <a href="{{ route('home') }}" class="text-lg font-bold text-gray-900 tracking-tight hover:text-indigo-600 transition">
                        Calorie Tracker
                    </a>
                @endauth
            </div>

            {{-- Desktop right side --}}
            <div class="hidden sm:flex sm:items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="text-sm {{ request()->routeIs('dashboard') ? 'text-indigo-600 font-medium' : 'text-gray-600 hover:text-indigo-600' }} transition">
                        Dashboard
                    </a>

                    <a href="{{ route('home') }}"
                       class="inline-flex items-center px-4 py-1.5 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium shadow-sm hover:opacity-90 transition">
                        Log Meal
                    </a>

                    <a href="{{ route('profile.edit') }}"
                       class="text-sm text-gray-600 hover:text-indigo-600 transition">
                        Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-sm text-gray-600 hover:text-red-500 transition">
                            Log out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="text-sm text-gray-600 hover:text-indigo-600 transition">
                        Log in
                    </a>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center px-4 py-1.5 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium shadow-sm hover:opacity-90 transition">
                        Sign up
                    </a>
                @endauth
            </div>

            {{-- Mobile hamburger --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition">
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
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-gray-200/60">
        <div class="px-6 py-4 space-y-3">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="block text-sm {{ request()->routeIs('dashboard') ? 'text-indigo-600 font-medium' : 'text-gray-600 hover:text-indigo-600' }} transition">
                    Dashboard
                </a>

                <a href="{{ route('home') }}"
                   class="block text-sm font-medium text-indigo-600 hover:text-indigo-700 transition">
                    Log Meal
                </a>

                <a href="{{ route('profile.edit') }}"
                   class="block text-sm text-gray-600 hover:text-indigo-600 transition">
                    Profile
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-600 hover:text-red-500 transition">
                        Log out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="block text-sm text-gray-600 hover:text-indigo-600 transition">
                    Log in
                </a>
                <a href="{{ route('register') }}"
                   class="block text-sm text-indigo-600 font-medium hover:text-indigo-700 transition">
                    Sign up
                </a>
            @endauth
        </div>
    </div>
</nav>
