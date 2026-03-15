<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShopCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('manager');
        
        // Фільтр по менеджеру
        if ($request->filled('manager_id')) {
            if ($request->manager_id === 'null') {
                $query->whereNull('manager_id');
            } else {
                $query->where('manager_id', $request->manager_id);
            }
        }
        
        // Фільтр по назві (пошук)
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Фільтр по мінімальній кількості
        if ($request->filled('min_quantity')) {
            $query->where('quantity', '>=', (int)$request->min_quantity);
        }
        
        // Фільтр по максимальній кількості
        if ($request->filled('max_quantity')) {
            $query->where('quantity', '<=', (int)$request->max_quantity);
        }
        
        $products = $query->with('variants')->orderBy('created_at', 'desc')->get();
        
        // Дані для фільтрів
        $managers = \App\Models\User::where('role', 'manager')->get();
        
        return view('admin.products.index', compact('products', 'managers'));
    }

    public function create()
    {
        $managers = User::where('role', 'manager')->get();
        $shopCategories = ShopCategory::orderBy('sort_order')->get();
        return view('admin.products.create', compact('managers', 'shopCategories'));
    }

    public function store(Request $request)
    {
        $request->merge(['shop_category' => $request->input('shop_category') ?: null]);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'manager_id' => 'nullable|exists:users,id',
            'available_in_bot' => 'nullable|boolean',
            'shop_category' => 'nullable|string|exists:shop_categories,slug',
        ]);
        $validated['available_in_bot'] = $request->boolean('available_in_bot');
        $validated['shop_category'] = $request->input('shop_category') ?: null;
        $validated['sale_price'] = $request->filled('sale_price') ? (float) $request->input('sale_price') : null;
        if (! Schema::hasColumn('products', 'description')) {
            unset($validated['description']);
        }
        if (! Schema::hasColumn('products', 'sale_price')) {
            unset($validated['sale_price']);
        }

        $imageError = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (!$file->isValid()) {
                $imageError = 'Файл не завантажився (можливо, завеликий для сервера). Спробуйте фото до 2 МБ.';
            } elseif ($file->getSize() > 5 * 1024 * 1024) {
                $imageError = 'Розмір фото не більше 5 МБ.';
            } else {
                $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');
                if (!in_array($ext, ['jpeg', 'jpg', 'png', 'gif', 'webp'], true)) {
                    $imageError = 'Дозволені формати: JPEG, PNG, GIF, WebP.';
                } else {
                    try {
                        Storage::disk('public')->makeDirectory('shop/products');
                        $path = $file->store('shop/products', 'public');
                        if ($path) {
                            $validated['image_path'] = $path;
                        }
                    } catch (\Throwable $e) {
                        $imageError = 'Не вдалося зберегти фото на сервері. Перевірте права на storage/app/public.';
                    }
                }
            }
        }

        $product = Product::create($validated);

        $variantsInput = $request->input('variants', []);
        if (is_array($variantsInput)) {
            $sortOrder = 0;
            foreach ($variantsInput as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                $qty = (int) ($row['quantity'] ?? 0);
                if ($name === '' && $qty <= 0) {
                    continue;
                }
                if ($name === '') {
                    $name = '—';
                }
                $product->variants()->create([
                    'name' => $name,
                    'quantity' => max(0, $qty),
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        $message = 'Товар успішно створений';
        if ($imageError) {
            return redirect()->route('admin.products.edit', $product)
                ->with('success', $message)
                ->with('warning', 'Фото не додано: ' . $imageError);
        }
        return redirect()->route('admin.products.index')
            ->with('success', $message);
    }

    public function show(Product $product)
    {
        $product->load('variants');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load('variants');
        $managers = User::where('role', 'manager')->get();
        $shopCategories = ShopCategory::orderBy('sort_order')->get();
        return view('admin.products.edit', compact('product', 'managers', 'shopCategories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->merge(['shop_category' => $request->input('shop_category') ?: null]);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'manager_id' => 'nullable|exists:users,id',
            'available_in_bot' => 'nullable|boolean',
            'shop_category' => 'nullable|string|exists:shop_categories,slug',
        ]);
        $validated['available_in_bot'] = $request->boolean('available_in_bot');
        $validated['shop_category'] = $request->input('shop_category') ?: null;
        $validated['sale_price'] = $request->filled('sale_price') ? (float) $request->input('sale_price') : null;
        if (! Schema::hasColumn('products', 'description')) {
            unset($validated['description']);
        }
        if (! Schema::hasColumn('products', 'sale_price')) {
            unset($validated['sale_price']);
        }

        $variantsInput = $request->input('variants', []);
        if (!is_array($variantsInput)) {
            $variantsInput = [];
        }

        $imageError = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (!$file->isValid()) {
                $imageError = 'Файл не завантажився (можливо, завеликий для сервера). Спробуйте фото до 2 МБ.';
            } elseif ($file->getSize() > 5 * 1024 * 1024) {
                $imageError = 'Розмір фото не більше 5 МБ.';
            } else {
                $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');
                if (!in_array($ext, ['jpeg', 'jpg', 'png', 'gif', 'webp'], true)) {
                    $imageError = 'Дозволені формати: JPEG, PNG, GIF, WebP.';
                } else {
                    try {
                        if ($product->image_path) {
                            Storage::disk('public')->delete($product->image_path);
                        }
                        Storage::disk('public')->makeDirectory('shop/products');
                        $path = $file->store('shop/products', 'public');
                        if ($path) {
                            $validated['image_path'] = $path;
                        }
                    } catch (\Throwable $e) {
                        $imageError = 'Не вдалося зберегти фото на сервері. Перевірте права на storage/app/public.';
                    }
                }
            }
        }

        $product->update($validated);

        $product->variants()->delete();
        $sortOrder = 0;
        foreach ($variantsInput as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $qty = (int) ($row['quantity'] ?? 0);
            if ($name === '' && $qty <= 0) {
                continue;
            }
            if ($name === '') {
                $name = '—';
            }
            $product->variants()->create([
                'name' => $name,
                'quantity' => max(0, $qty),
                'sort_order' => $sortOrder++,
            ]);
        }

        $message = 'Товар успішно оновлений';
        if ($imageError) {
            return redirect()->route('admin.products.edit', $product)
                ->with('success', $message)
                ->with('warning', 'Фото не оновлено: ' . $imageError);
        }
        return redirect()->route('admin.products.index')
            ->with('success', $message);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Товар успішно видалений');
    }
}
