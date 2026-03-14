<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        return '/storage/' . ltrim($this->image_path, '/');
    }

    public function productsCount(): int
    {
        return Product::where('available_in_bot', true)->where('shop_category', $this->slug)->count();
    }
}
