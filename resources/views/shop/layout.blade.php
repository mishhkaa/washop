<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Shop')) - CloudCity</title>
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}">
    <style>
        body { background: linear-gradient(160deg, #0f172a 0%, #0c1929 50%, #0e1a2e 100%) !important; color: #e2e8f0 !important; margin: 0; padding: 0; padding-top: 52px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .container { background: transparent !important; min-height: 100vh; }
        .cart-page, .cart-page-title { color: #f1f5f9 !important; }
        .empty-cart-wrap { background: transparent !important; min-height: 60vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
        .empty-cart-inner { text-align: center; max-width: 320px; }
        .empty-cart-title { color: #f1f5f9 !important; font-size: 20px; font-weight: 600; margin: 0 0 10px 0; }
        .empty-cart-text { color: #94a3b8 !important; font-size: 14px; margin: 0 0 24px 0; }
        .btn-empty-cart { display: inline-block; padding: 12px 24px; background: #2563eb; color: #fff !important; border-radius: 6px; font-size: 15px; text-decoration: none; }
        .btn-empty-cart:hover { background: #3b82f6; color: #fff !important; }
        .lang-switcher { display: flex; gap: 3px; align-items: stretch; }
        .lang-switcher a { flex: 1; min-width: 0; padding: 5px 6px; border-radius: 6px; font-size: 10px; font-weight: 600; text-decoration: none; color: #94a3b8; display: flex; align-items: center; justify-content: center; gap: 4px; }
        .lang-switcher a:hover { color: #e2e8f0; background: rgba(255,255,255,0.1); }
        .lang-switcher a.active { color: #93c5fd; background: rgba(59,130,246,0.25); }
        .lang-switcher .lang-flag { font-size: 1.15em; line-height: 1; }
    </style>
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
                    <a href="{{ route('shop.home') }}#about" class="nav-item">{{ __('About us') }}</a>
                    <a href="{{ route('shop.home', ['category' => 'pods']) }}" class="nav-item">{{ __('category.pods') }}</a>
                    <a href="{{ route('shop.home', ['category' => 'disposables']) }}" class="nav-item">{{ __('category.disposables') }}</a>
                    <a href="{{ route('shop.home', ['category' => 'liquids']) }}" class="nav-item">{{ __('category.liquids') }}</a>
                    <a href="{{ route('shop.home', ['category' => 'cartridges']) }}" class="nav-item">{{ __('category.cartridges') }}</a>
                    <a href="#" class="nav-item cart-open-trigger">{{ __('Cart') }}</a>
                </nav>
                <div class="header-actions">
                    <div class="lang-switcher">
                        <a href="{{ route('locale.switch', 'uk') }}" class="{{ app()->getLocale() === 'uk' ? 'active' : '' }}" title="Українська"><span class="lang-flag" aria-hidden="true">🇺🇦</span><span class="lang-code">УКР</span></a>
                        <a href="{{ route('locale.switch', 'pl') }}" class="{{ app()->getLocale() === 'pl' ? 'active' : '' }}" title="Polski"><span class="lang-flag" aria-hidden="true">🇵🇱</span><span class="lang-code">POL</span></a>
                        <a href="{{ route('locale.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}" title="English"><span class="lang-flag" aria-hidden="true">🇬🇧</span><span class="lang-code">ENG</span></a>
                    </div>
                    <button type="button" class="cart-icon cart-open-trigger" id="cartIcon" title="{{ __('Cart') }}" aria-label="{{ __('Cart') }}">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        <span class="cart-count" id="cartCount">{{ $cartCount ?? 0 }}</span>
                    </button>
                    <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-label="{{ __('Shop') }}">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>
            <nav class="mobile-menu" id="mobileMenu">
                <a href="{{ route('shop.home') }}#about">{{ __('About us') }}</a>
                <a href="{{ route('shop.home', ['category' => 'pods']) }}">{{ __('category.pods') }}</a>
                <a href="{{ route('shop.home', ['category' => 'liquids']) }}">{{ __('category.liquids') }}</a>
                <a href="{{ route('shop.home', ['category' => 'cartridges']) }}">{{ __('category.cartridges') }}</a>
                <a href="{{ route('shop.home', ['category' => 'disposables']) }}">{{ __('category.disposables') }}</a>
                <a href="#" class="cart-open-trigger">{{ __('Cart') }}</a>
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

        <footer class="site-footer">
            <div class="footer-inner">
                <div class="footer-row">
                    <nav class="footer-nav">
                        <a href="{{ route('shop.home') }}#about">{{ __('About us') }}</a>
                        <span class="footer-dot">·</span>
                        <a href="{{ route('shop.home') }}#delivery">{{ __('Delivery') }}</a>
                        <span class="footer-dot">·</span>
                        <a href="{{ route('shop.home', ['category' => 'pods']) }}">{{ __('category.pods') }}</a>
                        <span class="footer-dot">·</span>
                        <a href="{{ route('shop.home', ['category' => 'disposables']) }}">{{ __('category.disposables') }}</a>
                        <span class="footer-dot">·</span>
                        <a href="{{ route('shop.home', ['category' => 'liquids']) }}">{{ __('category.liquids') }}</a>
                        <span class="footer-dot">·</span>
                        <a href="{{ route('shop.home', ['category' => 'cartridges']) }}">{{ __('category.cartridges') }}</a>
                    </nav>
                    <a href="https://t.me/blvckPL" target="_blank" rel="noopener" class="footer-telegram">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18 1.897-.962 6.502-1.359 8.627-.168.9-.5 1.201-.82 1.23-.697.064-1.226-.461-1.901-.903-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.139-5.062 3.345-.479.329-.913.489-1.302.481-.428-.009-1.252-.241-1.865-.44-.752-.244-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.831-2.529 6.998-3.015 3.333-1.386 4.025-1.627 4.477-1.635.099-.002.321.023.465.141.121.1.154.234.17.33.015.096.034.313.02.483z"/></svg>
                        {{ __('Write to manager') }}
                    </a>
                </div>
                <div class="footer-brand">CloudCity</div>
            </div>
        </footer>
    </div>

    {{-- Кошик попапом --}}
    <div id="cartModal" class="cart-modal" aria-hidden="true">
        <div class="cart-modal-backdrop" id="cartModalBackdrop"></div>
        <div class="cart-modal-box" role="dialog" aria-labelledby="cartModalTitle">
            <div class="cart-modal-header">
                <h2 id="cartModalTitle" class="cart-modal-title">{{ __('Cart') }}</h2>
                <button type="button" class="cart-modal-close" id="cartModalClose" aria-label="{{ __('Close') }}">×</button>
            </div>
            @include('shop.partials.cart-popup')
        </div>
    </div>

    @stack('scripts')
    <script>
        document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('active');
            this.classList.toggle('active');
        });
        (function() {
            var modal = document.getElementById('cartModal');
            var closeBtn = document.getElementById('cartModalClose');
            var backdrop = document.getElementById('cartModalBackdrop');
            if (!modal) return;
            function openCart(e) { if (e) e.preventDefault(); modal.classList.add('cart-modal-open'); modal.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; }
            function closeCart() { modal.classList.remove('cart-modal-open'); modal.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; }
            document.querySelectorAll('.cart-open-trigger').forEach(function(trigger) { trigger.addEventListener('click', openCart); });
            closeBtn && closeBtn.addEventListener('click', closeCart);
            backdrop && backdrop.addEventListener('click', closeCart);
            document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && modal.classList.contains('cart-modal-open')) closeCart(); });
        })();
    </script>
</body>
</html>
