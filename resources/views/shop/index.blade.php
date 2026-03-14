@extends('shop.layout')

@section('title', 'Каталог')

@section('content')
<section class="search-section">
    <div class="search-container">
        <form method="GET" action="{{ route('shop.home') }}" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @if(request('price'))
                <input type="hidden" name="price" value="{{ request('price') }}">
            @endif
            <input type="text" name="search" class="search-input" placeholder="Пошук товарів..." value="{{ request('search') }}" style="flex: 1; min-width: 200px;">
            <button type="submit" class="btn-add" style="padding: 10px 20px;">Шукати</button>
        </form>
    </div>
</section>

<section class="categories-section">
    <div class="categories-container">
        @foreach($categories as $cat)
            <a href="{{ route('shop.home', request()->only('search', 'sort', 'price') + ['category' => $cat->slug]) }}" class="category-card {{ request('category') === $cat->slug ? 'active' : '' }}">
                <div class="category-image">
                    <img src="{{ $cat->image_path ? $cat->image_url : 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=400&h=400&fit=crop' }}" alt="{{ $cat->name }}">
                </div>
                <h3>{{ $cat->name }}</h3>
            </a>
        @endforeach
    </div>
</section>

<section class="filters-section">
    <div class="filters-header">
        <h3 class="filters-title">Фільтри</h3>
        <a href="{{ route('shop.home', request()->only('search', 'category')) }}" class="btn-reset">
            <span>🔄</span>
            <span>Скинути</span>
        </a>
    </div>
    <form method="GET" action="{{ route('shop.home') }}" class="filters-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
        @if(request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
        @endif
        <div class="filter-group">
            <div class="filter-icon">📊</div>
            <div class="filter-content">
                <label>Сортувати</label>
                <select name="sort" onchange="this.form.submit()">
                    <option value="default" {{ request('sort', 'default') === 'default' ? 'selected' : '' }}>За замовчуванням</option>
                    <option value="price-asc" {{ request('sort') === 'price-asc' ? 'selected' : '' }}>Ціна: від низької</option>
                    <option value="price-desc" {{ request('sort') === 'price-desc' ? 'selected' : '' }}>Ціна: від високої</option>
                    <option value="name-asc" {{ request('sort') === 'name-asc' ? 'selected' : '' }}>Назва: А–Я</option>
                    <option value="name-desc" {{ request('sort') === 'name-desc' ? 'selected' : '' }}>Назва: Я–А</option>
                </select>
            </div>
        </div>
        <div class="filter-group">
            <div class="filter-icon">💰</div>
            <div class="filter-content">
                <label>Ціна</label>
                <select name="price" onchange="this.form.submit()">
                    <option value="all" {{ request('price', 'all') === 'all' ? 'selected' : '' }}>Всі ціни</option>
                    <option value="0-50" {{ request('price') === '0-50' ? 'selected' : '' }}>До 50 zł</option>
                    <option value="50-80" {{ request('price') === '50-80' ? 'selected' : '' }}>50–80 zł</option>
                    <option value="80-100" {{ request('price') === '80-100' ? 'selected' : '' }}>80–100 zł</option>
                    <option value="100+" {{ request('price') === '100+' ? 'selected' : '' }}>Від 100 zł</option>
                </select>
            </div>
        </div>
    </form>
</section>

<section class="products" id="products">
    @forelse($products as $product)
        <div class="product-card">
            <div class="product-image">
                <img src="{{ $product->image_path ? $product->image_url : 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=400&h=400&fit=crop' }}" alt="{{ $product->name }}" onerror="this.src='https://via.placeholder.com/300?text=Product'">
            </div>
            <h3>{{ $product->name }}</h3>
            <p class="product-description">В наявності: {{ $product->quantity }} шт.</p>
            <div class="product-footer">
                <span class="price">{{ number_format($product->purchase_price ?? 0, 0) }} zł</span>
                <form action="{{ route('shop.cart.add') }}" method="POST" class="inline" style="display: inline;">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn-add">Додати</button>
                </form>
            </div>
        </div>
    @empty
        <div class="no-products">
            <p>Товарів не знайдено за обраними фільтрами.</p>
        </div>
    @endforelse
</section>

<section id="about" style="padding: 40px 20px; max-width: 800px; margin: 0 auto;">
    <h2 style="margin-bottom: 15px;">Про нас</h2>
    <p style="color: #636e72;">CloudCity — зручний вибір товарів. Обирайте категорію, додавайте в кошик та оформлюйте замовлення.</p>
</section>
<section id="delivery" style="padding: 20px; max-width: 800px; margin: 0 auto;">
    <h2 style="margin-bottom: 15px;">Доставка</h2>
    <p style="color: #636e72;">Умови доставки та оплати уточнюйте у менеджера.</p>
</section>
<section id="contacts" style="padding: 20px; max-width: 800px; margin: 0 auto;">
    <h2 style="margin-bottom: 15px;">Контакти</h2>
    <p style="color: #636e72;">Телеграм: <a href="https://t.me/blvckPL" target="_blank" rel="noopener">@blvckPL</a></p>
</section>
@endsection
