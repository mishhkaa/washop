<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Магазин') - CloudCity</title>
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
</head>
<body>
    <div class="container">
        <header class="sticky-header">
            <div class="header-content">
                <div class="logo">
                    <a href="{{ route('shop.home') }}" style="color: inherit; text-decoration: none;">
                        <h1>CloudCity</h1>
                    </a>
                </div>
                <nav class="header-nav desktop-only">
                    <a href="{{ route('shop.home') }}#about" class="nav-item">Про нас</a>
                    <a href="{{ route('shop.home', ['category' => 'pods']) }}" class="nav-item">Підсистеми</a>
                    <a href="{{ route('shop.home', ['category' => 'disposables']) }}" class="nav-item">Одноразки</a>
                    <a href="{{ route('shop.home', ['category' => 'liquids']) }}" class="nav-item">Рідини</a>
                    <a href="{{ route('shop.home', ['category' => 'cartridges']) }}" class="nav-item">Картриджі</a>
                    <a href="{{ route('shop.cart') }}" class="nav-item">Кошик</a>
                </nav>
                <div class="header-actions">
                    <a href="{{ route('shop.cart') }}" class="cart-icon" id="cartIcon" title="Кошик">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <span class="cart-count" id="cartCount">{{ $cartCount ?? 0 }}</span>
                    </a>
                    <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-label="Меню">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>
            <nav class="mobile-menu" id="mobileMenu">
                <a href="{{ route('shop.home') }}#about">Про нас</a>
                <a href="{{ route('shop.home', ['category' => 'pods']) }}">Підсистеми</a>
                <a href="{{ route('shop.home', ['category' => 'liquids']) }}">Рідини</a>
                <a href="{{ route('shop.home', ['category' => 'cartridges']) }}">Картриджі</a>
                <a href="{{ route('shop.home', ['category' => 'disposables']) }}">Одноразки</a>
                <a href="{{ route('shop.cart') }}">Кошик</a>
            </nav>
        </header>

        @if(session('success'))
            <div class="alert-success" style="margin: 15px; padding: 12px 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; color: #155724;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert-error" style="margin: 15px; padding: 12px 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24;">
                {{ session('error') }}
            </div>
        @endif
        @if(session('message'))
            <div style="margin: 15px; padding: 12px 20px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px; color: #856404;">
                {{ session('message') }}
            </div>
        @endif

        <main>
            @yield('content')
        </main>

        <footer>
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Інформація</h4>
                    <a href="{{ route('shop.home') }}#about">Про нас</a>
                    <a href="{{ route('shop.home') }}#delivery">Доставка</a>
                    <a href="{{ route('shop.home') }}#contacts">Контакти</a>
                </div>
                <div class="footer-divider"></div>
                <div class="footer-section">
                    <h4>Категорії</h4>
                    <a href="{{ route('shop.home', ['category' => 'pods']) }}">Підсистеми</a>
                    <a href="{{ route('shop.home', ['category' => 'disposables']) }}">Одноразки</a>
                    <a href="{{ route('shop.home', ['category' => 'liquids']) }}">Рідини</a>
                    <a href="{{ route('shop.home', ['category' => 'cartridges']) }}">Картриджі</a>
                </div>
                <div class="footer-divider"></div>
                <div class="footer-section">
                    <h4>Контакт</h4>
                    <a href="https://t.me/blvckPL" target="_blank" rel="noopener" class="footer-manager-btn" style="display: inline-block; text-align: center; text-decoration: none;">Написати менеджеру</a>
                </div>
            </div>
        </footer>
    </div>
    @stack('scripts')
    <script>
        document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('active');
            this.classList.toggle('active');
        });
    </script>
</body>
</html>
