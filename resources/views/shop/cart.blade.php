@extends('shop.layout')

@section('title', 'Кошик')

@section('content')
<section class="cart-page">
    <h2 class="cart-page-title">Кошик</h2>

    @if(empty($items))
        <div class="empty-cart-wrap">
            <div class="empty-cart-inner">
                <div class="empty-cart-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                </div>
                <h3 class="empty-cart-title">Кошик порожній</h3>
                <p class="empty-cart-text">Додайте товари з каталогу — вони з’являться тут.</p>
                <a href="{{ route('shop.home') }}" class="btn-empty-cart">Перейти до каталогу</a>
            </div>
        </div>
        @php return; @endphp
    @endif

    <ul class="cart-list">
        @foreach($items as $item)
            <li class="cart-item-card">
                <div class="cart-item-thumb">
                    <img src="{{ $item->product->image_path ? $item->product->image_url : 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=200&h=260&fit=crop' }}" alt="{{ $item->product->name }}">
                </div>
                <div class="cart-item-body">
                    <h4 class="cart-item-name">{{ $item->product->name }}</h4>
                    <p class="cart-item-meta">{{ number_format($item->product->purchase_price ?? 0, 0) }} zł × {{ $item->quantity }} шт.</p>
                    <div class="cart-item-row">
                        <span class="cart-item-total">{{ number_format(($item->product->purchase_price ?? 0) * $item->quantity, 0) }} zł</span>
                        <div class="cart-item-controls">
                            <form action="{{ route('shop.cart.update') }}" method="POST" class="cart-qty-form">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="cart-qty-input" onchange="this.form.submit()">
                            </form>
                            <form action="{{ route('shop.cart.remove') }}" method="POST" class="cart-remove-form">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                                <button type="submit" class="btn-cart-remove" title="Видалити" aria-label="Видалити">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="2" y1="2" x2="12" y2="12"/><line x1="12" y1="2" x2="2" y2="12"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    @php
        $total = 0;
        foreach ($items as $item) {
            $total += ($item->product->purchase_price ?? 0) * $item->quantity;
        }
    @endphp
    <div class="cart-footer">
        <p class="cart-footer-total">Разом: <strong>{{ number_format($total, 0) }} zł</strong></p>
        <a href="{{ route('shop.checkout.form') }}" class="btn-checkout-main">Оформити замовлення</a>
        <a href="{{ route('shop.home') }}" class="cart-footer-link">Продовжити покупки</a>
    </div>
</section>
@endsection
