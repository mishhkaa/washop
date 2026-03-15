<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Shop')) - CloudCity</title>
    {{-- secure_asset() щоб у Telegram WebView не блокувався CSS через mixed content --}}
    <link rel="stylesheet" href="{{ secure_asset('css/shop.css') }}">
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
        .lang-switcher { position: relative; }
        .lang-switcher__btn { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; color: #94a3b8; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); cursor: pointer; }
        .lang-switcher__btn:hover { color: #e2e8f0; background: rgba(255,255,255,0.1); }
        .lang-switcher__btn .lang-flag { font-size: 1.15em; }
        .lang-switcher__btn::after { content: ''; width: 0; height: 0; border-left: 4px solid transparent; border-right: 4px solid transparent; border-top: 5px solid currentColor; margin-left: 2px; opacity: 0.8; }
        .lang-switcher__drop { position: absolute; top: 100%; right: 0; margin-top: 4px; min-width: 100%; background: #1e293b; border: 1px solid #334155; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.3); padding: 4px; display: none; z-index: 100; }
        .lang-switcher__drop.open { display: block; }
        .lang-switcher__drop a { display: flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; color: #94a3b8; text-decoration: none; }
        .lang-switcher__drop a:hover { color: #fff; background: rgba(255,255,255,0.1); }
        .lang-switcher__drop a.active { color: #93c5fd; background: rgba(59,130,246,0.2); }
        /* Чекаут: звичний вигляд форми (і в браузері, і в Telegram) */
        .checkout-page { color: #e2e8f0 !important; }
        .checkout-page .checkout-title,
        .checkout-page .checkout-section-title { color: #f1f5f9 !important; font-weight: 600 !important; }
        .checkout-page .checkout-total-label,
        .checkout-page .checkout-item { color: #e2e8f0 !important; }
        .checkout-page .checkout-section,
        .checkout-page .checkout-cashback-block { background: #1e293b !important; border: 1px solid #334155 !important; border-radius: 12px !important; }
        .checkout-page .delivery-option { background: #0f172a !important; border: 1px solid #475569 !important; border-radius: 8px !important; }
        .checkout-page .delivery-option:has(input:checked) { border-color: #2563eb !important; background: #1e293b !important; box-shadow: 0 0 0 2px rgba(37,99,235,0.25) !important; }
        .checkout-page .delivery-option-label { color: #f1f5f9 !important; font-weight: 600 !important; font-size: 15px !important; }
        .checkout-page .delivery-option-note { color: #94a3b8 !important; }
        .checkout-page .delivery-option input[type="radio"] { accent-color: #2563eb !important; }
        .checkout-page .checkout-delivery-fields { border-top: 1px solid #334155 !important; margin-top: 20px !important; padding-top: 20px !important; }
        .checkout-page .checkout-label { color: #e2e8f0 !important; font-weight: 600 !important; font-size: 14px !important; margin-bottom: 8px !important; margin-top: 18px !important; }
        .checkout-page .checkout-label:first-child { margin-top: 0 !important; }
        .checkout-page .checkout-input,
        .checkout-page input[type="text"],
        .checkout-page input[type="number"] { background: #0f172a !important; color: #e2e8f0 !important; border: 1px solid #475569 !important; border-radius: 8px !important; padding: 12px 16px !important; font-size: 15px !important; -webkit-appearance: none !important; appearance: none !important; width: 100% !important; box-sizing: border-box !important; }
        .checkout-page .checkout-input:focus,
        .checkout-page input:focus { border-color: #2563eb !important; box-shadow: 0 0 0 2px rgba(37,99,235,0.25) !important; outline: none !important; }
        .checkout-page .checkout-input::placeholder,
        .checkout-page input::placeholder { color: #64748b !important; }
        .checkout-page .checkout-link-inline { color: #60a5fa !important; margin-top: 12px !important; font-size: 14px !important; }
        .checkout-page .checkout-error { color: #f87171 !important; }
        .checkout-page .checkout-cashback-hint { color: #94a3b8 !important; }
        .checkout-page .btn-checkout-submit { background: #2563eb !important; color: #fff !important; border: none !important; border-radius: 8px !important; font-size: 16px !important; }
        .checkout-page .checkout-back-link,
        .checkout-page .checkout-link-back { color: #60a5fa !important; }
    </style>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
</head>
<body @if(session('open_cart')) data-open-cart="1" @endif>
    <script>
    (function(){if(typeof Telegram!=='undefined'&&Telegram.WebApp){document.body.classList.add('tg-webapp');}else{window.addEventListener('load',function(){if(typeof Telegram!=='undefined'&&Telegram.WebApp){document.body.classList.add('tg-webapp');}});var t=0;var iv=setInterval(function(){t++;if(typeof Telegram!=='undefined'&&Telegram.WebApp){document.body.classList.add('tg-webapp');clearInterval(iv);}else if(t>40){clearInterval(iv);}},50);})();
    </script>
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
                    @foreach($shopCategories ?? [] as $cat)
                        <a href="{{ route('shop.home', ['category' => $cat->slug]) }}" class="nav-item">{{ $cat->name }}</a>
                    @endforeach
                    <a href="#" class="nav-item cart-open-trigger">{{ __('Cart') }}</a>
                </nav>
                <div class="header-actions">
                    @php
                        $locale = app()->getLocale();
                        $langs = [
                            'pl' => ['flag' => '🇵🇱', 'code' => 'POL', 'title' => 'Polski'],
                            'uk' => ['flag' => '🇺🇦', 'code' => 'УКР', 'title' => 'Українська'],
                            'en' => ['flag' => '🇬🇧', 'code' => 'ENG', 'title' => 'English'],
                        ];
                        $current = $langs[$locale] ?? $langs['pl'];
                    @endphp
                    <div class="lang-switcher" id="langSwitcher">
                        <button type="button" class="lang-switcher__btn" id="langSwitcherBtn" aria-expanded="false" aria-haspopup="true" aria-label="{{ $current['title'] }}">
                            <span class="lang-flag">{{ $current['flag'] }}</span>
                            <span class="lang-code">{{ $current['code'] }}</span>
                        </button>
                        <div class="lang-switcher__drop" id="langSwitcherDrop" role="menu">
                            @foreach($langs as $code => $item)
                                <a href="{{ route('locale.switch', $code) }}" role="menuitem" class="{{ $locale === $code ? 'active' : '' }}" title="{{ $item['title'] }}">{{ $item['flag'] }} {{ $item['code'] }}</a>
                            @endforeach
                        </div>
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
                @foreach($shopCategories ?? [] as $cat)
                    <a href="{{ route('shop.home', ['category' => $cat->slug]) }}">{{ $cat->name }}</a>
                @endforeach
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
                        @foreach($shopCategories ?? [] as $cat)
                            <span class="footer-dot">·</span>
                            <a href="{{ route('shop.home', ['category' => $cat->slug]) }}">{{ $cat->name }}</a>
                        @endforeach
                    </nav>
                    <a href="https://t.me/CloudCityManagerr" target="_blank" rel="noopener" class="footer-telegram">
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
            if (document.body.dataset.openCart === '1') {
                openCart();
                delete document.body.dataset.openCart;
            }
        })();
        (function() {
            if (typeof Telegram === 'undefined' || !Telegram.WebApp) return;
            var wa = Telegram.WebApp;
            wa.ready();
            if (wa.setHeaderColor) wa.setHeaderColor('#0f172a');
            if (wa.setBackgroundColor) wa.setBackgroundColor('#0f172a');
            if (wa.expand) wa.expand();
        })();
        (function() {
            if (typeof Telegram === 'undefined' || !Telegram.WebApp || !Telegram.WebApp.initDataUnsafe || !Telegram.WebApp.initDataUnsafe.user) return;
            var u = Telegram.WebApp.initDataUnsafe.user;
            if (!u.id && !u.username) return;
            var token = document.querySelector('meta[name="csrf-token"]');
            if (!token) return;
            fetch('{{ url("/shop/set-telegram") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token.getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify({ telegram_user_id: u.id ? String(u.id) : '', telegram_username: u.username ? String(u.username) : '' })
            }).catch(function() {});
        })();
        (function() {
            var wrap = document.getElementById('langSwitcher');
            var btn = document.getElementById('langSwitcherBtn');
            var drop = document.getElementById('langSwitcherDrop');
            if (!wrap || !btn || !drop) return;
            btn.addEventListener('click', function(e) { e.stopPropagation(); drop.classList.toggle('open'); btn.setAttribute('aria-expanded', drop.classList.contains('open')); });
            document.addEventListener('click', function() { drop.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); });
            wrap.addEventListener('click', function(e) { e.stopPropagation(); });
        })();
    </script>
</body>
</html>
