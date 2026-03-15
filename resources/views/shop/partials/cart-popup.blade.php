@php
    $items = $cartItems ?? [];
    $total = 0;
    foreach ($items as $item) {
        $total += ($item->product->website_price ?? 0) * $item->quantity;
    }
@endphp
<div class="cart-modal-body">
    @if(empty($items))
        <div class="cart-popup-empty">
            <div class="cart-popup-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </div>
            <p class="cart-popup-empty-title">{{ __('Cart empty') }}</p>
            <p class="cart-popup-empty-text">{{ __('Cart empty text') }}</p>
            <a href="{{ route('shop.home') }}" class="btn-empty-cart">{{ __('Go to catalog') }}</a>
        </div>
    @else
        <ul class="cart-popup-list">
            @foreach($items as $item)
                <li class="cart-popup-item">
                    <div class="cart-popup-thumb">
                        <img src="{{ $item->product->image_path ? $item->product->image_url : 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=100&h=100&fit=crop' }}" alt="{{ $item->product->display_name }}">
                    </div>
                    <div class="cart-popup-info">
                        <span class="cart-popup-name">{{ $item->product->display_name }}@if($item->variant_name) · {{ $item->variant_name }}@endif</span>
                        <span class="cart-popup-meta">{{ number_format($item->product->website_price ?? 0, 0) }} zł</span>
                    </div>
                    <div class="cart-popup-qty-wrap">
                        <form action="{{ route('shop.cart.update') }}" method="POST" class="cart-popup-qty-form">
                            @csrf
                            <input type="hidden" name="cart_key" value="{{ $item->cart_key }}">
                            <input type="hidden" name="quantity" value="{{ max(0, $item->quantity - 1) }}">
                            <button type="submit" class="btn-qty btn-qty-minus" title="-1" aria-label="-1">−</button>
                        </form>
                        <span class="cart-popup-qty-num">{{ $item->quantity }}</span>
                        <form action="{{ route('shop.cart.update') }}" method="POST" class="cart-popup-qty-form">
                            @csrf
                            <input type="hidden" name="cart_key" value="{{ $item->cart_key }}">
                            <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                            <button type="submit" class="btn-qty btn-qty-plus" title="+1" aria-label="+1">+</button>
                        </form>
                    </div>
                    <div class="cart-popup-right">
                        <span class="cart-popup-total">{{ number_format(($item->product->website_price ?? 0) * $item->quantity, 0) }} zł</span>
                        <form action="{{ route('shop.cart.remove') }}" method="POST" class="cart-popup-remove-form">
                            @csrf
                            <input type="hidden" name="cart_key" value="{{ $item->cart_key }}">
                            <button type="submit" class="btn-cart-popup-remove" title="{{ __('Remove') }}" aria-label="{{ __('Remove') }}">
                                <svg width="12" height="12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2"><line x1="2" y1="2" x2="12" y2="12"/><line x1="12" y1="2" x2="2" y2="12"/></svg>
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="cart-popup-footer">
            <p class="cart-popup-total-label">{{ __('Total') }}: <strong>{{ number_format($total, 0) }} zł</strong></p>
            <a href="{{ route('shop.checkout.form') }}" class="btn-checkout-popup">{{ __('Place order') }}</a>
        </div>
    @endif
</div>
