<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'purchase_price',
        'quantity',
        'manager_id',
        'available_in_bot',
        'shop_category',
        'image_path',
    ];

    protected $casts = [
        'available_in_bot' => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('name');
    }

    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    /** Варіанти з залишком > 0 (для вибору в кошику) */
    public function getAvailableVariantsAttribute()
    {
        return $this->variants()->where('quantity', '>', 0)->get();
    }

    /** Загальна кількість: сума по смаках або quantity товару */
    public function getTotalQuantityAttribute(): int
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (int) $this->variants->sum('quantity');
        }
        if ($this->hasVariants()) {
            return (int) $this->variants()->sum('quantity');
        }
        return (int) ($this->quantity ?? 0);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        // Відносний шлях — зображення завжди з того ж хоста/порту, що й сторінка
        return '/storage/' . ltrim($this->image_path, '/');
    }

    /** Назва для відображення: для категорії Pody додається позначка про рідину в подарунок */
    public function getDisplayNameAttribute(): string
    {
        $suffix = ($this->shop_category === 'pods') ? ' (' . __('+ liquid as gift') . ')' : '';
        return $this->name . $suffix;
    }
}
