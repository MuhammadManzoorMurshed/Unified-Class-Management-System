<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>UCMS - @yield('title', 'App')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900 flex" data-page="@yield('page-id')">

    @php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    $roleName = $user && isset($user->role)
    ? ($user->role->role_name ?? 'User')
    : 'User';

    $nameForInitial = $user->name ?? 'U';
    // multibyte safe initial
    $initials = strtoupper(mb_substr($nameForInitial, 0, 1, 'UTF-8'));

    // unreadMessages সাধারণত dashboard view থেকে আসবে, না এলে fallback 0
    $unreadMessages = $unreadMessages ?? 0;
    @endphp

    {{-- Left sidebar (global navigation) --}}
    <aside
        class="w-16 md:w-20 bg-gradient-to-b from-slate-900 to-slate-800 text-slate-200 flex flex-col items-center py-6 space-y-8 relative group hover:w-64 transition-all duration-300 ease-in-out overflow-hidden">

        {{-- Expanded Background Overlay --}}
        <div
            class="absolute inset-0 bg-slate-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10">
        </div>

        {{-- Logo Section --}}
        <div
            class="flex items-center justify-center w-14 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg transform group-hover:scale-110 transition-transform duration-300">
            <span class="text-white font-bold text-sm">UCMS</span>
        </div>

        {{-- Navigation Menu --}}
        <nav id="main-nav" class="flex-1 flex flex-col items-center w-full space-y-3 px-2">
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" data-nav="dashboard"
                class="nav-item group relative w-full flex items-center gap-4 px-3 py-3 rounded-2xl hover:bg-white/10 transition-all duration-200 hover:translate-x-1">
                <div
                    class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shadow-inner backdrop-blur-sm group-hover:bg-indigo-500/20 transition-colors">
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-white" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <span
                    class="text-sm font-medium text-slate-300 group-hover:text-white whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute left-16">
                    Dashboard
                </span>
            </a>

            {{-- My Classes --}}
            <a href="{{ route('classes.index') }}" data-nav="classes.index"
                class="nav-item group relative w-full flex items-center gap-4 px-3 py-3 rounded-2xl hover:bg-white/10 transition-all duration-200 hover:translate-x-1">
                <div
                    class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shadow-inner backdrop-blur-sm group-hover:bg-green-500/20 transition-colors">
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-white" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span
                    class="text-sm font-medium text-slate-300 group-hover:text-white whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute left-16">
                    My Classes
                </span>
            </a>

            {{-- Messages --}}
            <a href="#" data-nav="messages"
                class="nav-item group relative w-full flex items-center gap-4 px-3 py-3 rounded-2xl hover:bg-white/10 transition-all duration-200 hover:translate-x-1">
                <div
                    class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shadow-inner backdrop-blur-sm group-hover:bg-blue-500/20 transition-colors">
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-white" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <span
                    class="text-sm font-medium text-slate-300 group-hover:text-white whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute left-16">
                    Messages
                </span>
            </a>

            {{-- Assignments --}}
            <a href="#" data-nav="assignments"
                class="nav-item group relative w-full flex items-center gap-4 px-3 py-3 rounded-2xl hover:bg-white/10 transition-all duration-200 hover:translate-x-1">
                <div
                    class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shadow-inner backdrop-blur-sm group-hover:bg-purple-500/20 transition-colors">
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-white" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span
                    class="text-sm font-medium text-slate-300 group-hover:text-white whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute left-16">
                    Assignments
                </span>
            </a>

            {{-- Grades --}}
            <a href="#" data-nav="grades"
                class="nav-item group relative w-full flex items-center gap-4 px-3 py-3 rounded-2xl hover:bg-white/10 transition-all duration-200 hover:translate-x-1">
                <div
                    class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shadow-inner backdrop-blur-sm group-hover:bg-emerald-500/20 transition-colors">
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-white" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <span
                    class="text-sm font-medium text-slate-300 group-hover:text-white whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute left-16">
                    Grades
                </span>
            </a>

            {{-- Calendar --}}
            <a href="#" data-nav="calendar"
                class="nav-item group relative w-full flex items-center gap-4 px-3 py-3 rounded-2xl hover:bg-white/10 transition-all duration-200 hover:translate-x-1">
                <div
                    class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shadow-inner backdrop-blur-sm group-hover:bg-orange-500/20 transition-colors">
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-white" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span
                    class="text-sm font-medium text-slate-300 group-hover:text-white whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute left-16">
                    Calendar
                </span>
            </a>
        </nav>

        {{-- Bottom Section --}}
        <div class="flex flex-col items-center w-full space-y-3 px-2 mt-auto">
            {{-- Settings --}}
            <a href="#" data-nav="settings"
                class="nav-item group relative w-full flex items-center gap-4 px-3 py-3 rounded-2xl hover:bg-white/10 transition-all duration-200 hover:translate-x-1">
                <div
                    class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shadow-inner backdrop-blur-sm group-hover:bg-slate-500/20 transition-colors">
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-white" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <span
                    class="text-sm font-medium text-slate-300 group-hover:text-white whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute left-16">
                    Settings
                </span>
            </a>

            {{-- Profile --}}
            <a href="#" data-nav="profile"
                class="nav-item group relative w-full flex items-center gap-4 px-3 py-3 rounded-2xl hover:bg-white/10 transition-all duration-200 hover:translate-x-1">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center shadow-inner backdrop-blur-sm border border-white/10">
                    <svg class="w-5 h-5 text-slate-300 group-hover:text-white" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <span
                    class="text-sm font-medium text-slate-300 group-hover:text-white whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute left-16">
                    Profile
                </span>
            </a>
        </div>

        {{-- Active State Indicator --}}
        <div
            class="absolute left-0 top-0 h-full w-1 bg-gradient-to-b from-indigo-500 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        </div>
    </aside>

    @push('scripts')
    <script>
    // Active navigation state management
    document.addEventListener('DOMContentLoaded', function() {
        const navItems = document.querySelectorAll('.nav-item');
        const currentPage = '{{ Route::currentRouteName() }}';

        navItems.forEach(item => {
            const navTarget = item.getAttribute('data-nav');

            // Set active state based on current route
            if (navTarget === currentPage || currentPage.includes(navTarget)) {
                item.classList.add('bg-white/10', 'translate-x-1');
                item.querySelector('div').classList.remove('bg-white/10');
                item.querySelector('div').classList.add('bg-indigo-500/30');
                item.querySelector('svg').classList.add('text-white');
            }

            // Add click handlers for smooth interactions
            item.addEventListener('click', function(e) {
                // Remove active states from all items
                navItems.forEach(nav => {
                    nav.classList.remove('bg-white/10', 'translate-x-1');
                    nav.querySelector('div').classList.remove('bg-indigo-500/30');
                    nav.querySelector('div').classList.add('bg-white/10');
                    nav.querySelector('svg').classList.remove('text-white');
                    nav.querySelector('svg').classList.add('text-slate-300');
                });

                // Add active state to clicked item
                this.classList.add('bg-white/10', 'translate-x-1');
                this.querySelector('div').classList.remove('bg-white/10');
                this.querySelector('div').classList.add('bg-indigo-500/30');
                this.querySelector('svg').classList.remove('text-slate-300');
                this.querySelector('svg').classList.add('text-white');
            });
        });
    });
    </script>
    @endpush

    <style>
    /* Smooth scrolling and selection */
    #main-nav {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    #main-nav::-webkit-scrollbar {
        display: none;
    }

    /* Custom hover effects */
    .nav-item {
        position: relative;
        overflow: hidden;
    }

    .nav-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transition: left 0.5s;
    }

    .nav-item:hover::before {
        left: 100%;
    }
    </style>

    {{-- Main area --}}
    <div class="flex-1 flex flex-col min-h-screen">

        {{-- Top header --}}
        <header
            class="h-16 bg-white/95 backdrop-blur-sm border-b border-slate-200 flex items-center justify-between px-4 md:px-6 sticky top-0 z-50">

            {{-- Left: University Logo + Page Title --}}
            <div class="flex items-center gap-4">
                {{-- University Logo --}}
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/puc_logo.png') }}" alt="University Logo"
                        class="w-10 h-10 rounded-lg object-contain shadow-sm">
                    <div class="hidden md:block h-6 w-px bg-slate-300/70"></div>
                </div>

                {{-- Page Title --}}
                <div class="flex flex-col">
                    <h1 class="text-lg md:text-xl font-semibold text-indigo-600 leading-tight mb-1">
                        @yield('header-title', 'Unified Class Management System')
                    </h1>
                    <span class="hidden md:inline-block text-xs text-slate-500">
                        @yield('header-subtitle', 'Manage your classes efficiently')
                    </span>
                </div>
            </div>

            {{-- Right: Tools + User --}}
            <div class="flex items-center gap-3">
                {{-- Quick Tools --}}
                <div class="hidden md:flex items-center gap-1">
                    {{-- Search --}}
                    <button title="Search" class="p-2 rounded-lg hover:bg-slate-100 transition-colors group">
                        <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-700" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>

                    {{-- Notifications with Proper Bell Icon --}}
                    <button title="Notifications"
                        class="relative p-2 rounded-lg hover:bg-slate-100 transition-colors group">
                        <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-700" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-5 5v-5zM10.24 8.56a5.97 5.97 0 01-4.66-7.5 1 1 0 00-1.2-1.2 7.97 7.97 0 006.16 10.02 1 1 0 001.2-1.2 5.97 5.97 0 01-1.5-4.66z" />
                        </svg>
                        @if(isset($unreadNotifications) && $unreadNotifications > 0)
                        <div
                            class="absolute -top-1 -right-1 w-4 h-4 bg-red-600 text-white text-[10px] rounded-full flex items-center justify-center font-bold shadow">
                            {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                        </div>
                        @endif
                    </button>

                    {{-- Settings --}}
                    <button title="Settings" class="p-2 rounded-lg hover:bg-slate-100 transition-colors group">
                        <svg class="w-5 h-5 text-slate-500 group-hover:text-slate-700" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>

                {{-- User Section --}}
                <div class="flex items-center gap-2">
                    {{-- User Icon --}}
                    <div
                        class="w-9 h-9 rounded-full bg-slate-100 border-2 border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-200 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>

                    {{-- Logout Button --}}
                    <form method="POST" action="{{ route('logout') }}" class="flex items-center" id="logout-form">
                        @csrf
                        <button type="submit"
                            class="p-2 text-indigo-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors group"
                            title="Logout" id="btn-logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </header>



        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto">
            @yield('content')
        </main>
    </div>
</body>

</html>