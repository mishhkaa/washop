@extends('shop.layout')

@section('title', 'Оформлення замовлення')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <h2 style="margin-bottom: 20px;">Оформлення замовлення</h2>

    <ul style="list-style: none; padding: 0; margin-bottom: 25px;">
        @foreach($items as $item)
            <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
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
    <p style="font-size: 18px; margin-bottom: 25px;"><strong>До сплати: {{ number_format($total, 0) }} zł</strong></p>

    <form action="{{ route('shop.checkout') }}" method="POST" id="checkoutForm">
        @csrf
        {{-- Підставляється автоматично з Telegram WebApp --}}
        <input type="hidden" name="telegram_user_id" id="telegram_user_id" value="">
        <input type="hidden" name="telegram_username" id="telegram_username" value="">
        <button type="submit" class="btn-add" style="padding: 14px 28px; cursor: pointer;">Підтвердити замовлення</button>
    </form>

    <a href="{{ route('shop.cart') }}" style="display: inline-block; margin-top: 15px; color: #1976d2;">Повернутися до кошика</a>
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
