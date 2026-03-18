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
                {{ $item->product->display_name }}@if($item->variant_name) · {{ $item->variant_name }}@endif × {{ $item->quantity }} — {{ number_format($item->product->website_price * $item->quantity, 0) }} zł
            </li>
        @endforeach
    </ul>

    @php
        $total = $orderTotal ?? 0;
        if ($total <= 0) {
            foreach ($items as $item) {
                $total += $item->product->website_price * $item->quantity;
            }
        }
        $cashbackBalance = $client ? (float) $client->cashback_balance : 0;
        $maxUseCashback = min($cashbackBalance, $total);
    @endphp
    <p class="checkout-total-label">{{ __('Order total') }}: <strong>{{ number_format($total, 2) }} zł</strong></p>

    <form action="{{ route('shop.checkout') }}" method="POST" id="checkoutForm" class="checkout-form">
        @csrf
        <input type="hidden" name="telegram_user_id" id="telegram_user_id" value="">
        <input type="hidden" name="telegram_username" id="telegram_username" value="">

        @if($client && $cashbackBalance > 0)
        <section class="checkout-section checkout-cashback-block">
            <h3 class="checkout-section-title">{{ __('Your cashback') }}: {{ number_format($cashbackBalance, 2) }} zł</h3>
            <label class="checkout-label" for="use_cashback">{{ __('Use cashback') }}</label>
            <input type="number" name="use_cashback" id="use_cashback" class="checkout-input" value="{{ old('use_cashback', 0) }}" min="0" max="{{ number_format($maxUseCashback, 2, '.', '') }}" step="0.01" placeholder="0">
            <p class="checkout-cashback-hint">{{ __('Max to use') }}: {{ number_format($maxUseCashback, 2) }} zł</p>
            @if($errors->has('use_cashback'))
            <p class="checkout-error">{{ $errors->first('use_cashback') }}</p>
            @endif
        </section>
        @endif

        <section class="checkout-section">
            @php
                $method = old('delivery_method', $deliveryMethodForm ?? 'paczkomat');
                $isPaczkomat = $method === 'paczkomat';
                $isPickup = $method === 'osobisty_odbior';
                $districtValue = old('delivery_pickup_district', $districtFromSession ?? '');
            @endphp

            <input type="hidden" name="delivery_method" value="{{ $method }}">
            @if($isPickup)
                <input type="hidden" name="delivery_pickup_district" value="{{ $districtValue }}">
            @endif

            <h3 class="checkout-section-title">{{ __('Delivery method') }}</h3>
            <p class="checkout-item" style="margin-top: 0;">
                @if($isPaczkomat)
                    <strong>{{ __('Paczkomat InPost') }}</strong>
                @else
                    <strong>{{ __('Personal pickup') }}</strong> · {{ $districtValue ?: '—' }}
                @endif
            </p>

            @if($errors->has('delivery_method'))
                <p class="checkout-error">{{ $errors->first('delivery_method') }}</p>
            @endif

            @if($isPaczkomat)
            <div id="paczkomat-fields" class="checkout-delivery-fields">
                <label class="checkout-label" for="delivery_paczkomat_name">{{ __('Your name') }}</label>
                <input type="text" name="delivery_pickup_name" id="delivery_paczkomat_name" class="checkout-input" value="{{ old('delivery_pickup_name') }}" maxlength="255" placeholder="{{ __('Your name') }}">
                @if($errors->has('delivery_pickup_name'))
                <p class="checkout-error">{{ $errors->first('delivery_pickup_name') }}</p>
                @endif
                <label class="checkout-label" for="delivery_paczkomat_phone">{{ __('Phone') }}</label>
                <input type="text" name="delivery_pickup_phone" id="delivery_paczkomat_phone" class="checkout-input" value="{{ old('delivery_pickup_phone') }}" maxlength="64" placeholder="{{ __('Phone placeholder') }}">
                @if($errors->has('delivery_pickup_phone'))
                <p class="checkout-error">{{ $errors->first('delivery_pickup_phone') }}</p>
                @endif
                <label class="checkout-label" for="delivery_paczkomat_code">{{ __('Paczkomat code') }}</label>
                <input type="text" name="delivery_paczkomat_code" id="delivery_paczkomat_code" class="checkout-input" placeholder="{{ __('Paczkomat code placeholder') }}" value="{{ old('delivery_paczkomat_code') }}" maxlength="32" autocomplete="off">
                <a href="https://inpost.pl/znajdz-paczkomat" target="_blank" rel="noopener noreferrer" class="checkout-link-inline">{{ __('Find paczkomat on map') }}</a>
                @if($errors->has('delivery_paczkomat_code'))
                <p class="checkout-error">{{ $errors->first('delivery_paczkomat_code') }}</p>
                @endif
            </div>
            @endif

            @if($isPickup)
            <div id="pickup-fields" class="checkout-delivery-fields">
                <label class="checkout-label" for="delivery_pickup_name">{{ __('Your name') }}</label>
                <input type="text" name="delivery_pickup_name" id="delivery_pickup_name" class="checkout-input" value="{{ old('delivery_pickup_name') }}" maxlength="255">
                @if($errors->has('delivery_pickup_name'))
                <p class="checkout-error">{{ $errors->first('delivery_pickup_name') }}</p>
                @endif
                <label class="checkout-label" for="delivery_pickup_phone">{{ __('Phone') }}</label>
                <input type="text" name="delivery_pickup_phone" id="delivery_pickup_phone" class="checkout-input" value="{{ old('delivery_pickup_phone') }}" maxlength="64" placeholder="{{ __('Phone placeholder') }}">
                @if($errors->has('delivery_pickup_phone'))
                <p class="checkout-error">{{ $errors->first('delivery_pickup_phone') }}</p>
                @endif
                <label class="checkout-label">{{ __('District / area') }}</label>
                <div class="checkout-input" style="display:flex; align-items:center; min-height: 48px;">{{ $districtValue ?: '—' }}</div>
                @if($errors->has('delivery_pickup_district'))
                <p class="checkout-error">{{ $errors->first('delivery_pickup_district') }}</p>
                @endif
            </div>
            @endif
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
