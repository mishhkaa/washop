@extends('shop.layout')

@section('title', __('Order'))

@section('content')
<div class="checkout-page">
    <div class="checkout-back">
        <a href="{{ route('shop.home') }}" class="checkout-back-link">← {{ __('Back to cart') }}</a>
    </div>
    <h2 class="checkout-title">{{ __('Order') }}</h2>

    <ul class="checkout-items">
        @foreach($items as $item)
            <li class="checkout-item">
                {{ $item->product->name }} × {{ $item->quantity }} — {{ number_format($item->product->purchase_price * $item->quantity, 0) }} zł
            </li>
        @endforeach
    </ul>

    @php
        $total = 0;
        foreach ($items as $item) {
            $total += $item->product->purchase_price * $item->quantity;
        }
    @endphp
    <p class="checkout-total-label">{{ __('Order total') }}: <strong>{{ number_format($total, 0) }} zł</strong></p>

    <form action="{{ route('shop.checkout') }}" method="POST" id="checkoutForm" class="checkout-form">
        @csrf
        <input type="hidden" name="telegram_user_id" id="telegram_user_id" value="">
        <input type="hidden" name="telegram_username" id="telegram_username" value="">

        <section class="checkout-section">
            <h3 class="checkout-section-title">{{ __('Choose delivery method') }}</h3>
            @if($errors->has('delivery_method'))
            <p class="checkout-error">{{ $errors->first('delivery_method') }}</p>
            @endif
            <div class="delivery-options">
                <label class="delivery-option">
                    <input type="radio" name="delivery_method" value="paczkomat" {{ old('delivery_method', 'paczkomat') === 'paczkomat' ? 'checked' : '' }}>
                    <span class="delivery-option-label">{{ __('Paczkomat InPost') }}</span>
                    <span class="delivery-option-note">{{ __('Payment on delivery') }}</span>
                </label>
                <label class="delivery-option">
                    <input type="radio" name="delivery_method" value="osobisty_odbior" {{ old('delivery_method') === 'osobisty_odbior' ? 'checked' : '' }}>
                    <span class="delivery-option-label">{{ __('Personal pickup') }}</span>
                    <span class="delivery-option-note">{{ __('Pickup at point') }}</span>
                </label>
            </div>
        </section>

        <div class="checkout-actions">
            <button type="submit" class="btn-checkout-submit">{{ __('Confirm order') }}</button>
        </div>
    </form>

    <a href="{{ route('shop.home') }}" class="checkout-link-back">{{ __('Back to cart') }}</a>
</div>

@push('scripts')
<script>
(function() {
    var uid = document.getElementById('telegram_user_id');
    var uname = document.getElementById('telegram_username');
    if (typeof Telegram !== 'undefined' && Telegram.WebApp && Telegram.WebApp.initDataUnsafe && Telegram.WebApp.initDataUnsafe.user) {
        var u = Telegram.WebApp.initDataUnsafe.user;
        if (u.id && uid) uid.value = String(u.id);
        if (u.username && uname) uname.value = String(u.username);
    }
    if (typeof Telegram !== 'undefined' && Telegram.WebApp && Telegram.WebApp.expand) {
        Telegram.WebApp.expand();
    }
})();
</script>
@endpush
@endsection
