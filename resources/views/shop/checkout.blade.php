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
                {{ $item->product->display_name }}@if($item->variant_name) · {{ $item->variant_name }}@endif × {{ $item->quantity }} — {{ number_format($item->product->purchase_price * $item->quantity, 0) }} zł
            </li>
        @endforeach
    </ul>

    @php
        $total = $orderTotal ?? 0;
        if ($total <= 0) {
            foreach ($items as $item) {
                $total += $item->product->purchase_price * $item->quantity;
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
            <h3 class="checkout-section-title">{{ __('Choose delivery method') }}</h3>
            @if($errors->has('delivery_method'))
            <p class="checkout-error">{{ $errors->first('delivery_method') }}</p>
            @endif
            <div class="delivery-options">
                <label class="delivery-option">
                    <input type="radio" name="delivery_method" value="paczkomat" id="delivery_paczkomat" {{ old('delivery_method', 'paczkomat') === 'paczkomat' ? 'checked' : '' }}>
                    <span class="delivery-option-label">{{ __('Paczkomat InPost') }}</span>
                    <span class="delivery-option-note">{{ __('Payment on delivery') }}</span>
                </label>
                <label class="delivery-option">
                    <input type="radio" name="delivery_method" value="osobisty_odbior" id="delivery_osobisty" {{ old('delivery_method') === 'osobisty_odbior' ? 'checked' : '' }}>
                    <span class="delivery-option-label">{{ __('Personal pickup') }}</span>
                    <span class="delivery-option-note">{{ __('Pickup at point') }}</span>
                </label>
            </div>

            <div id="paczkomat-fields" class="checkout-delivery-fields">
                <label class="checkout-label" for="delivery_paczkomat_code">{{ __('Paczkomat code') }}</label>
                <input type="text" name="delivery_paczkomat_code" id="delivery_paczkomat_code" class="checkout-input" placeholder="{{ __('Paczkomat code placeholder') }}" value="{{ old('delivery_paczkomat_code') }}" maxlength="32" autocomplete="off">
                <a href="https://inpost.pl/znajdz-paczkomat" target="_blank" rel="noopener noreferrer" class="checkout-link-inline">{{ __('Find paczkomat on map') }}</a>
                @if($errors->has('delivery_paczkomat_code'))
                <p class="checkout-error">{{ $errors->first('delivery_paczkomat_code') }}</p>
                @endif
            </div>

            <div id="pickup-fields" class="checkout-delivery-fields" style="display: none;">
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
                <label class="checkout-label" for="delivery_pickup_district">{{ __('District / area') }}</label>
                @php
                    $districtOptions = ['Ursynów', 'Praga'];
                    $districtValue = old('delivery_pickup_district');
                    $isOtherDistrict = $districtValue === 'other' || ($districtValue && !in_array($districtValue, $districtOptions, true));
                @endphp
                <select name="delivery_pickup_district" id="delivery_pickup_district" class="checkout-input">
                    <option value="">— {{ __('Choose district') }} —</option>
                    @foreach($districtOptions as $opt)
                    <option value="{{ $opt }}" {{ $districtValue === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                    <option value="other" {{ $isOtherDistrict ? 'selected' : '' }}>{{ __('Other district') }}</option>
                </select>
                <div id="delivery_pickup_district_other_wrap" class="checkout-delivery-fields" style="display: none; margin-top: 0.5rem;">
                    <label class="checkout-label" for="delivery_pickup_district_other">{{ __('Other district (specify)') }}</label>
                    <input type="text" name="delivery_pickup_district_other" id="delivery_pickup_district_other" class="checkout-input" value="{{ old('delivery_pickup_district_other', $isOtherDistrict ? $districtValue : '') }}" maxlength="255" placeholder="{{ __('e.g. Bielany') }}">
                </div>
                @if($errors->has('delivery_pickup_district'))
                <p class="checkout-error">{{ $errors->first('delivery_pickup_district') }}</p>
                @endif
                @if($errors->has('delivery_pickup_district_other'))
                <p class="checkout-error">{{ $errors->first('delivery_pickup_district_other') }}</p>
                @endif
                <label class="checkout-label" for="delivery_pickup_day">{{ __('Preferred pickup day') }}</label>
                <input type="text" name="delivery_pickup_day" id="delivery_pickup_day" class="checkout-input" value="{{ old('delivery_pickup_day') }}" maxlength="255" placeholder="{{ __('Preferred pickup day placeholder') }}">
                @if($errors->has('delivery_pickup_day'))
                <p class="checkout-error">{{ $errors->first('delivery_pickup_day') }}</p>
                @endif
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

    function toggleDeliveryFields() {
        var method = document.querySelector('input[name="delivery_method"]:checked');
        var paczkomatBlock = document.getElementById('paczkomat-fields');
        var pickupBlock = document.getElementById('pickup-fields');
        if (!method || !paczkomatBlock || !pickupBlock) return;
        if (method.value === 'paczkomat') {
            paczkomatBlock.style.display = 'block';
            pickupBlock.style.display = 'none';
        } else {
            paczkomatBlock.style.display = 'none';
            pickupBlock.style.display = 'block';
        }
        var districtSelect = document.getElementById('delivery_pickup_district');
        var otherWrap = document.getElementById('delivery_pickup_district_other_wrap');
        if (districtSelect && otherWrap) {
            otherWrap.style.display = districtSelect.value === 'other' ? 'block' : 'none';
        }
    }
    function toggleDistrictOther() {
        var districtSelect = document.getElementById('delivery_pickup_district');
        var otherWrap = document.getElementById('delivery_pickup_district_other_wrap');
        if (districtSelect && otherWrap) {
            otherWrap.style.display = districtSelect.value === 'other' ? 'block' : 'none';
        }
    }
    document.querySelectorAll('input[name="delivery_method"]').forEach(function(radio) {
        radio.addEventListener('change', toggleDeliveryFields);
    });
    var districtSelect = document.getElementById('delivery_pickup_district');
    if (districtSelect) districtSelect.addEventListener('change', toggleDistrictOther);
    toggleDeliveryFields();
})();
</script>
@endpush
@endsection
