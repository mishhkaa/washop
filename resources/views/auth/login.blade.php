<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app-fallback.css') }}">
    @endif
</head>
<body>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
            <!-- Login Form Card -->
            <div class="bg-white rounded-xl shadow-xl border border-gray-100" style="padding: 2rem 2rem;">
                <!-- Header -->
                <div class="text-center" style="margin-bottom: 1.5rem;">
                    <div class="mx-auto bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg" style="width: 4rem; height: 4rem; margin-bottom: 1rem;">
                        <svg class="text-white" style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900" style="margin-bottom: 0.5rem;">Вхід</h1>
                    <p class="text-sm text-gray-600">Введіть дані для входу в систему</p>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm" style="margin-bottom: 1.5rem; padding: 0.75rem;">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" style="margin: 0; padding: 0;">
                    @csrf

                    <!-- Email -->
                    <div style="margin-bottom: 1.25rem !important;">
                        <label for="email" class="block text-sm font-medium text-gray-700" style="margin-bottom: 0.5rem !important;">
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus
                            autocomplete="email"
                            style="width: 100%; padding: 0.75rem 1rem !important; border-width: 1px; border-color: rgb(209 213 219); border-radius: 0.5rem; outline: none; font-size: 1rem; transition: all 0.2s;"
                            class="focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="your@email.com"
                        >
                    </div>

                    <!-- Password -->
                    <div style="margin-bottom: 1.25rem;">
                        <label for="password" class="block text-sm font-medium text-gray-700" style="margin-bottom: 0.5rem !important;">
                            Пароль
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            autocomplete="current-password"
                            style="width: 100%; padding: 0.75rem 1rem !important; border-width: 1px; border-color: rgb(209 213 219); border-radius: 0.5rem; outline: none; font-size: 1rem; transition: all 0.2s;"
                            class="focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="••••••••"
                        >
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center" style="margin-bottom: 1.5rem;">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember"
                            class="text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            style="width: 1rem; height: 1rem;"
                        >
                        <label for="remember" class="text-sm text-gray-700" style="margin-left: 0.5rem;">
                            Запам'ятати мене
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button 
                            type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-md hover:shadow-lg text-base"
                            style="padding: 0.75rem 1.5rem;"
                        >
                            Увійти
                        </button>
                    </div>
                </form>

                <!-- Footer -->
                <div class="text-center text-sm text-gray-500" style="margin-top: 1.5rem;">
                    <a href="{{ route('shop.home') }}" class="text-blue-600 hover:underline">На головну (магазин)</a> &middot; CRM © {{ date('Y') }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
