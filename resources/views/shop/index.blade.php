@extends('shop.layout')

@section('title', __('Catalog'))

@section('content')
@php
    $baseParams = request()->only('category', 'search', 'sort', 'price');
    $districtUrsynow = \App\Models\Product::DISTRICT_URSYNOW;
    $districtPraga = \App\Models\Product::DISTRICT_PRAGA;
@endphp
@if(!empty($showDeliveryChoiceModal))
    <section style="padding: 20px; max-width: 800px; margin: 0 auto;">
        <div style="background: rgba(15,23,42,0.55); border: 1px solid rgba(148,163,184,0.15); border-radius: 14px; padding: 18px;">
            <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #f1f5f9;">{{ __('Choose delivery method') }}</h2>
            <p style="margin: 0; color: #94a3b8; font-size: 13px;">{{ __('Please choose delivery method') }}</p>
        </div>
    </section>
@else
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
            <input type="hidden" name="delivery_method" value="{{ $deliveryMethod ?? 'paczkomat' }}">
            @if(isset($district) && $district)
                <input type="hidden" name="district" value="{{ $district }}">
            @endif
            <input type="text" name="search" class="search-input" placeholder="{{ __('Search products placeholder') }}" value="{{ request('search') }}" style="flex: 1; min-width: 200px;">
            <button type="submit" class="btn-add" style="padding: 10px 20px;">{{ __('Search') }}</button>
        </form>
    </div>
</section>

<section class="categories-section">
    <div class="categories-container">
        @foreach($categories as $cat)
            @php $catParams = array_merge(request()->only('search', 'sort', 'price'), ['category' => $cat->slug]); if (isset($deliveryMethod)) { $catParams['delivery_method'] = $deliveryMethod; } if (!empty($district)) { $catParams['district'] = $district; } @endphp
            <a href="{{ route('shop.home', $catParams) }}" class="category-card {{ request('category') === $cat->slug ? 'active' : '' }}">
                <div class="category-image">
                    <img src="{{ $cat->image_path ? $cat->image_url : 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=400&h=400&fit=crop' }}" alt="{{ $cat->name }}">
                </div>
                <h3>{{ $cat->name }}</h3>
            </a>
        @endforeach
    </div>
</section>

<section class="filters-section">
    <form method="GET" action="{{ route('shop.home') }}" class="filters-row">
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
        @if(request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
        @endif
        <input type="hidden" name="delivery_method" value="{{ $deliveryMethod ?? 'paczkomat' }}">
        @if(isset($district) && $district)
            <input type="hidden" name="district" value="{{ $district }}">
        @endif
        <span class="filters-label">{{ __('Sorting') }}</span>
        <select name="sort" onchange="this.form.submit()" class="filters-select">
            <option value="default" {{ request('sort', 'default') === 'default' ? 'selected' : '' }}>{{ __('Default') }}</option>
            <option value="name-asc" {{ request('sort') === 'name-asc' ? 'selected' : '' }}>{{ __('Name A-Z') }}</option>
            <option value="name-desc" {{ request('sort') === 'name-desc' ? 'selected' : '' }}>{{ __('Name Z-A') }}</option>
            <option value="price-asc" {{ request('sort') === 'price-asc' ? 'selected' : '' }}>{{ __('Price ascending') }}</option>
            <option value="price-desc" {{ request('sort') === 'price-desc' ? 'selected' : '' }}>{{ __('Price descending') }}</option>
        </select>
        @php $resetParams = request()->only('search', 'category'); if (isset($deliveryMethod)) { $resetParams['delivery_method'] = $deliveryMethod; } if (!empty($district)) { $resetParams['district'] = $district; } @endphp
        <a href="{{ route('shop.home', $resetParams) }}" class="filters-reset" title="{{ __('Reset') }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            {{ __('Reset') }}
        </a>
    </form>
</section>

<section class="products" id="products">
    @forelse($products as $product)
        <div class="product-card">
            <div class="product-image">
                <img src="{{ $product->image_path ? $product->image_url : 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=400&h=400&fit=crop' }}" alt="{{ $product->display_name }}" onerror="this.src='https://via.placeholder.com/300?text=Product'">
            </div>
            <h3 class="product-card__title">{{ $product->display_name }}</h3>
            <p class="product-card__desc">{{ $product->description ? Str::limit($product->description, 60) : '—' }}</p>
            @php $availableVariants = $product->getAvailableVariantsForDistrict($district ?? null); @endphp
            @if($product->hasVariants() && $availableVariants->isNotEmpty())
                <div class="product-flavor">
                    <label class="product-flavor__label" for="variant-{{ $product->id }}">{{ __('Flavor') }}</label>
                    <select name="variant_id" id="variant-{{ $product->id }}" class="product-variant-select" required form="product-form-{{ $product->id }}">
                        <option value="">{{ __('Choose flavor') }}</option>
                        @foreach($availableVariants as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="product-footer">
                <span class="price">{{ number_format($product->website_price ?? 0, 0) }} zł</span>
                <form id="product-form-{{ $product->id }}" action="{{ route('shop.cart.add') }}" method="POST" class="product-add-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    @if(!$product->hasVariants() || $availableVariants->isEmpty())
                        <input type="hidden" name="variant_id" value="0">
                    @endif
                    <button type="submit" class="btn-add">{{ __('Add') }}</button>
                </form>
            </div>
        </div>
    @empty
        <div class="no-products">
            <p>{{ __('No products found') }}</p>
        </div>
    @endforelse
</section>
@endif

<section id="about" class="landing-section" style="padding: 40px 20px; max-width: 800px; margin: 0 auto;">
    <h2 style="margin-bottom: 15px; color: #f1f5f9;">{{ __('About us') }}</h2>
    <p style="color: #94a3b8;">{{ __('About us text') }}</p>
</section>
<section id="delivery" class="landing-section" style="padding: 20px; max-width: 800px; margin: 0 auto;">
    <h2 style="margin-bottom: 15px; color: #f1f5f9;">{{ __('Delivery') }}</h2>
    <p style="color: #94a3b8;">{{ __('Delivery text') }}</p>
</section>
<section id="contacts" class="landing-section" style="padding: 20px; max-width: 800px; margin: 0 auto;">
    <h2 style="margin-bottom: 15px; color: #f1f5f9;">{{ __('Contact') }}</h2>
    <p style="color: #94a3b8;">Telegram: <a href="https://t.me/CloudCityManagerr" target="_blank" rel="noopener" style="color: #60a5fa;">@CloudCityManagerr</a></p>
</section>
@endsection
