<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use Database\Seeders\ShopCategoriesSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShopCategoryController extends Controller
{
    public function index()
    {
        $categories = ShopCategory::orderBy('sort_order')->orderBy('name')->get();
        if ($categories->isEmpty()) {
            (new ShopCategoriesSeeder())->run();
            $categories = ShopCategory::orderBy('sort_order')->orderBy('name')->get();
        }
        return view('admin.shop.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.shop.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:shop_categories,slug|regex:/^[a-z0-9_-]+$/',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'image.file' => 'Оберіть файл зображення.',
            'image.mimes' => 'Дозволені формати: JPEG, PNG, GIF, WebP.',
            'image.max' => 'Розмір фото не більше 5 МБ.',
        ]);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (!$file->isValid()) {
                return back()->withInput()->withErrors(['image' => 'Файл не завантажився (можливо, завеликий). Спробуйте фото до 2–3 МБ.']);
            }
            try {
                Storage::disk('public')->makeDirectory('shop/categories');
                $validated['image_path'] = $file->store('shop/categories', 'public');
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors(['image' => 'Не вдалося зберегти фото. Перевірте права на папку storage/app/public.']);
            }
        }

        ShopCategory::create($validated);

        return redirect()->route('admin.shop.categories.index')
            ->with('success', 'Категорію магазину створено');
    }

    public function edit(ShopCategory $shopCategory)
    {
        return view('admin.shop.categories.edit', ['category' => $shopCategory]);
    }

    public function update(Request $request, ShopCategory $shopCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|regex:/^[a-z0-9_-]+$/|unique:shop_categories,slug,' . $shopCategory->id,
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'image.file' => 'Оберіть файл зображення.',
            'image.mimes' => 'Дозволені формати: JPEG, PNG, GIF, WebP.',
            'image.max' => 'Розмір фото не більше 5 МБ.',
        ]);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (!$file->isValid()) {
                return back()->withInput()->withErrors(['image' => 'Файл не завантажився (можливо, завеликий). Спробуйте фото до 2–3 МБ.']);
            }
            try {
                if ($shopCategory->image_path) {
                    Storage::disk('public')->delete($shopCategory->image_path);
                }
                Storage::disk('public')->makeDirectory('shop/categories');
                $validated['image_path'] = $file->store('shop/categories', 'public');
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors(['image' => 'Не вдалося зберегти фото. Перевірте права на папку storage/app/public.']);
            }
        }

        $shopCategory->update($validated);

        return redirect()->route('admin.shop.categories.index')
            ->with('success', 'Категорію оновлено');
    }

    public function destroy(ShopCategory $shopCategory)
    {
        if ($shopCategory->image_path) {
            Storage::disk('public')->delete($shopCategory->image_path);
        }
        $shopCategory->delete();
        return redirect()->route('admin.shop.categories.index')
            ->with('success', 'Категорію видалено');
    }
}
