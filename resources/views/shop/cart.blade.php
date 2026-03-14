@extends('shop.layout')

@section('title', 'Кошик')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <h2 style="margin-bottom: 20px;">Кошик</h2>

    @if(empty($items))
        <p>Кошик порожній.</p>
        <a href="{{ route('shop.home') }}" class="btn-add" style="display: inline-block; margin-top: 15px; text-decoration: none;">Перейти до каталогу</a>
        @php return; @endphp
    @endif

    <ul style="list-style: none; padding: 0;">
        @foreach($items as $item)
            <li style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #e0e0e0; flex-wrap: wrap; gap: 10px;">
                <div>
                    <strong>{{ $item->product->name }}</strong>
                    <span style="color: #636e72;"> × {{ $item->quantity }}</span>
                    — {{ number_format($item->product->purchase_price * $item->quantity, 0) }} zł
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <form action="{{ route('shop.cart.update') }}" method="POST" style="display: inline-flex; align-items: center; gap: 5px;">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" style="width: 60px; padding: 6px;">
                        <button type="submit" class="btn-add" style="padding: 6px 12px; font-size: 14px;">Ок</button>
                    </form>
                    <form action="{{ route('shop.cart.remove') }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                        <button type="submit" style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer;">Видалити</button>
                    </form>
                </div>
            </li>
        @endforeach
    </ul>

    @php
        $total = 0;
        foreach ($items as $item) {
            $total += $item->product->purchase_price * $item->quantity;
        }
    @endphp
    <p style="margin-top: 20px; font-size: 20px;"><strong>Разом: {{ number_format($total, 0) }} zł</strong></p>
    <a href="{{ route('shop.checkout.form') }}" class="btn-add" style="display: inline-block; margin-top: 15px; padding: 14px 28px; text-decoration: none;">Оформити замовлення</a>
    <a href="{{ route('shop.home') }}" style="display: inline-block; margin-left: 10px; margin-top: 15px; color: #1976d2;">Продовжити покупки</a>
</div>
@endsection
