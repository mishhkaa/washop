<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CRM System - Менеджер')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <!-- Styles -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app-fallback.css') }}">
    @endif
</head>
<body>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('manager.dashboard') }}" class="text-xl font-bold text-blue-600 hover:text-blue-700">
                            CRM - Менеджер
                        </a>
                        <!-- Desktop Navigation -->
                        <div class="hidden md:flex md:ml-10 md:space-x-4">
                            <a href="{{ route('manager.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('manager.dashboard') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('manager.sales.create') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('manager.sales.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                                Додати продаж
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="hidden sm:block text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                        <!-- Mobile menu button -->
                        <button type="button" class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" id="mobile-menu-button">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                            @csrf
                            <button type="submit" class="btn-secondary">
                                Вийти
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Mobile menu -->
            <div class="hidden md:hidden border-t border-gray-200" id="mobile-menu">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('manager.dashboard') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('manager.dashboard') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">Dashboard</a>
                    <a href="{{ route('manager.sales.create') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('manager.sales.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">Додати продаж</a>
                    <div class="border-t border-gray-200 pt-4 pb-3">
                        <div class="px-3">
                            <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                        </div>
                        <div class="mt-3 px-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full btn-secondary">
                                    Вийти
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:py-8 px-4 sm:px-6 lg:px-8">
            @yield('content')
        </main>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu?.classList.toggle('hidden');
        });
    </script>
</body>
</html>
