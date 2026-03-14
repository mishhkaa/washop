@extends('layouts.app')

@section('title', 'Додати продаж')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Додати продаж</h1>
        <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Створіть новий продаж</p>
    </div>

    <div class="card max-w-2xl">
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('manager.sales.store') }}" method="POST" id="saleForm">
            @csrf

            <!-- Комбінований продаж -->
            <div class="form-group">
                <label class="flex items-center">
                    <input type="checkbox" id="is_combined" name="is_combined" value="1" {{ old('is_combined') ? 'checked' : '' }} onchange="toggleCombined()">
                    <span class="ml-2">Комбінований продаж (кілька товарів)</span>
                </label>
            </div>

            <!-- Звичайний продаж (один товар) -->
            <div id="regularSale">
                <div class="form-group">
                    <label for="product_id">Товар</label>
                    <select id="product_id" name="product_id">
                        <option value="">Оберіть товар</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->purchase_price ?? 0 }}" data-quantity="{{ $product->quantity ?? 0 }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} (залишок: {{ $product->quantity ?? 0 }} шт.)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">Кількість</label>
                    <input type="number" id="quantity" name="quantity" min="1" value="{{ old('quantity') }}" placeholder="1">
                </div>

                <div class="form-group">
                    <label for="sale_price">Ціна за одиницю (zł)</label>
                    <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" value="{{ old('sale_price') }}" placeholder="0.00">
                </div>
            </div>

            <!-- Комбінований продаж (кілька товарів) -->
            <div id="combinedSale" style="display: none;">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Товари в комбінованому продажу</h3>
                    <div id="saleItems">
                        <!-- Товари будуть додані тут через JavaScript -->
                    </div>
                    <button type="button" onclick="addSaleItem()" class="btn-secondary mt-3">
                        + Додати товар
                    </button>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 sm:gap-4 flex-wrap mt-8 pt-6 border-t border-gray-200">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">
                    Зберегти продаж
                </button>
                <a href="{{ route('manager.dashboard') }}" class="btn-secondary flex-1 sm:flex-none text-center">
                    Скасувати
                </a>
            </div>
        </form>

        <script>
            let itemCounter = 0;
            const products = @json($products);

            function toggleCombined() {
                const isCombined = document.getElementById('is_combined').checked;
                const regularSale = document.getElementById('regularSale');
                const combinedSale = document.getElementById('combinedSale');

                if (isCombined) {
                    regularSale.style.display = 'none';
                    combinedSale.style.display = 'block';
                    // Знімаємо required з полів звичайного продажу
                    document.getElementById('product_id').removeAttribute('required');
                    document.getElementById('quantity').removeAttribute('required');
                    document.getElementById('sale_price').removeAttribute('required');
                    // Додаємо перший товар
                    if (itemCounter === 0) {
                        addSaleItem();
                    }
                } else {
                    regularSale.style.display = 'block';
                    combinedSale.style.display = 'none';
                    // Додаємо required до полів звичайного продажу
                    document.getElementById('product_id').setAttribute('required', 'required');
                    document.getElementById('quantity').setAttribute('required', 'required');
                    document.getElementById('sale_price').setAttribute('required', 'required');
                }
            }

            function addSaleItem() {
                itemCounter++;
                const container = document.getElementById('saleItems');
                const itemDiv = document.createElement('div');
                itemDiv.className = 'mb-4 p-4 border border-gray-200 rounded-lg';
                itemDiv.id = `item-${itemCounter}`;

                let productOptions = '<option value="">Оберіть товар</option>';
                products.forEach(product => {
                    productOptions += `<option value="${product.id}" data-price="${product.purchase_price || 0}" data-quantity="${product.quantity || 0}">
                        ${product.name} (залишок: ${product.quantity || 0} шт.)
                    </option>`;
                });

                itemDiv.innerHTML = `
                    <div class="flex items-start justify-between mb-3">
                        <h4 class="font-semibold text-gray-900">Товар ${itemCounter}</h4>
                        <button type="button" onclick="removeSaleItem(${itemCounter})" class="text-red-600 hover:text-red-700">
                            Видалити
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label>Товар</label>
                            <select name="sale_items[${itemCounter}][product_id]" required class="product-select">
                                ${productOptions}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Кількість</label>
                            <input type="number" name="sale_items[${itemCounter}][quantity]" min="1" value="1" required placeholder="1">
                        </div>
                        <div class="form-group">
                            <label>Ціна продажу (zł)</label>
                            <input type="number" name="sale_items[${itemCounter}][sale_price]" step="0.01" min="0" required placeholder="0.00">
                        </div>
                    </div>
                `;

                container.appendChild(itemDiv);
            }

            function removeSaleItem(id) {
                const item = document.getElementById(`item-${id}`);
                if (item) {
                    item.remove();
                }
            }

            // Ініціалізація при завантаженні сторінки
            document.addEventListener('DOMContentLoaded', function() {
                toggleCombined();
            });
        </script>
    </div>
</div>
@endsection
