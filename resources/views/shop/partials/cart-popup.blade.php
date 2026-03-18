@php
    $items = $cartItems ?? [];
    $total = 0;
    $deliveryMethod = session('shop_delivery_method', 'paczkomat');
    $district = session('shop_delivery_district');
    $districtForStock = ($deliveryMethod === 'osobisty') ? $district : null;
    $districtOptions = [\App\Models\Product::DISTRICT_URSYNOW, \App\Models\Product::DISTRICT_PRAGA];
    $unavailable = [];
    foreach ($items as $item) {
        $total += ($item->product->website_price ?? 0) * $item->quantity;
        $availableQty = 0;
        if ($item->variant && $item->variant->id) {
            $availableQty = $item->product->getVariantQuantityForDistrict((int) $item->variant->id, $districtForStock);
        } else {
            $availableQty = $item->product->getQuantityForDistrict($districtForStock);
        }
        if ($availableQty < (int) $item->quantity) {
            $unavailable[$item->cart_key] = $availableQty;
        }
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
        <div style="margin-bottom: 12px; padding: 12px; border-radius: 12px; border: 1px solid rgba(148,163,184,0.25); background: rgba(15,23,42,0.45);">
            <form action="{{ route('shop.cart.delivery') }}" method="POST" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
                @csrf
                <div style="flex:1; min-width: 180px;">
                    <label style="display:block; font-size: 12px; color: #94a3b8; margin-bottom: 6px;">{{ __('Delivery method') }}</label>
                    <select name="delivery_method" id="cart_delivery_method" class="checkout-input" style="padding: 10px 12px;">
                        <option value="paczkomat" {{ $deliveryMethod === 'paczkomat' ? 'selected' : '' }}>{{ __('Paczkomat InPost') }}</option>
                        <option value="osobisty" {{ $deliveryMethod === 'osobisty' ? 'selected' : '' }}>{{ __('Personal pickup') }}</option>
                    </select>
                </div>
                <div style="flex:1; min-width: 180px; {{ $deliveryMethod === 'osobisty' ? '' : 'display:none;' }}" id="cart_district_wrap">
                    <label style="display:block; font-size: 12px; color: #94a3b8; margin-bottom: 6px;">{{ __('District / area') }}</label>
                    <select name="district" class="checkout-input" style="padding: 10px 12px;">
                        <option value="">— {{ __('Choose district') }} —</option>
                        @foreach($districtOptions as $opt)
                            <option value="{{ $opt }}" {{ ($district ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-add" style="padding: 10px 16px;">OK</button>
            </form>
            @if(!empty($unavailable))
                <p style="margin: 10px 0 0 0; color: #fca5a5; font-size: 12px;">
                    {{ __('Insufficient product') }}
                </p>
            @endif
        </div>
        <ul class="cart-popup-list">
            @foreach($items as $item)
                <li class="cart-popup-item">
                    <div class="cart-popup-thumb">
                        <img src="{{ $item->product->image_path ? $item->product->image_url : 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=100&h=100&fit=crop' }}" alt="{{ $item->product->display_name }}">
                    </div>
                    <div class="cart-popup-info">
                        <span class="cart-popup-name">{{ $item->product->display_name }}@if($item->variant_name) · {{ $item->variant_name }}@endif</span>
                        <span class="cart-popup-meta">{{ number_format($item->product->website_price ?? 0, 0) }} zł</span>
                        @if(array_key_exists($item->cart_key, $unavailable))
                            <span style="margin-top: 4px; display:block; font-size: 12px; color: #fca5a5;">
                                Немає в обраному районі/режимі. Доступно: {{ (int) $unavailable[$item->cart_key] }}.
                            </span>
                        @endif
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
            @if(empty($unavailable))
                <a href="{{ route('shop.checkout.form') }}" class="btn-checkout-popup">{{ __('Place order') }}</a>
            @else
                <span class="btn-checkout-popup" style="opacity:0.55; cursor:not-allowed;">{{ __('Place order') }}</span>
            @endif
        </div>
    @endif
</div>

<script>
(function() {
    var m = document.getElementById('cart_delivery_method');
    var w = document.getElementById('cart_district_wrap');
    if (!m || !w) return;
    m.addEventListener('change', function() {
        w.style.display = (m.value === 'osobisty') ? '' : 'none';
    });
})();
</script>
