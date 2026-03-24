<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'purchase_price',
        'sale_price',
        'quantity',
        'quantity_ursynow',
        'quantity_praga',
        'manager_id',
        'available_in_bot',
        'shop_category',
        'image_path',
    ];

    /** Райони самовивозу (константи для перевірок) */
    public const DISTRICT_URSYNOW = 'Ursynów';
    public const DISTRICT_PRAGA = 'Praga';

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

    /** Варіанти з залишком > 0 для району (або загальний quantity якщо район null) */
    public function getAvailableVariantsForDistrict(?string $district = null)
    {
        if (!Schema::hasColumn('product_variants', 'quantity_ursynow')) {
            return $this->variants()->where('quantity', '>', 0)->orderBy('sort_order')->orderBy('name')->get();
        }
        if ($district === self::DISTRICT_URSYNOW) {
            return $this->variants()->where('quantity_ursynow', '>', 0)->orderBy('sort_order')->orderBy('name')->get();
        }
        if ($district === self::DISTRICT_PRAGA) {
            return $this->variants()->where('quantity_praga', '>', 0)->orderBy('sort_order')->orderBy('name')->get();
        }
        return $this->variants()->whereRaw('(quantity_ursynow + quantity_praga) > 0')->orderBy('sort_order')->orderBy('name')->get();
    }

    /** Застаріло: варіанти з quantity > 0 (для зворотної сумісності) */
    public function getAvailableVariantsAttribute()
    {
        if (Schema::hasColumn('product_variants', 'quantity_ursynow')) {
            return $this->variants()->whereRaw('(quantity_ursynow + quantity_praga) > 0')->orderBy('sort_order')->orderBy('name')->get();
        }
        return $this->variants()->where('quantity', '>', 0)->orderBy('sort_order')->orderBy('name')->get();
    }

    /** Кількість для району (або сума обох якщо district null). Без варіантів — з продукту, з варіантами — сума по варіантах. */
    public function getQuantityForDistrict(?string $district = null): int
    {
        if ($this->hasVariants()) {
            if ($district === self::DISTRICT_URSYNOW && Schema::hasColumn('product_variants', 'quantity_ursynow')) {
                return (int) $this->variants()->sum('quantity_ursynow');
            }
            if ($district === self::DISTRICT_PRAGA && Schema::hasColumn('product_variants', 'quantity_praga')) {
                return (int) $this->variants()->sum('quantity_praga');
            }
            if ($district === null && Schema::hasColumn('product_variants', 'quantity_ursynow')) {
                return (int) $this->variants()->selectRaw('SUM(quantity_ursynow + quantity_praga) as t')->value('t');
            }
            return (int) $this->variants()->sum('quantity');
        }
        if ($district === self::DISTRICT_URSYNOW && Schema::hasColumn('products', 'quantity_ursynow')) {
            return (int) ($this->quantity_ursynow ?? 0);
        }
        if ($district === self::DISTRICT_PRAGA && Schema::hasColumn('products', 'quantity_praga')) {
            return (int) ($this->quantity_praga ?? 0);
        }
        if ($district === null && Schema::hasColumn('products', 'quantity_ursynow')) {
            return (int) (($this->quantity_ursynow ?? 0) + ($this->quantity_praga ?? 0));
        }
        return (int) ($this->quantity ?? 0);
    }

    /** Кількість конкретного варіанта для району (або сума обох). */
    public function getVariantQuantityForDistrict(int $variantId, ?string $district = null): int
    {
        $v = $this->variants()->where('id', $variantId)->first();
        if (!$v) {
            return 0;
        }
        if (!Schema::hasColumn('product_variants', 'quantity_ursynow')) {
            return (int) ($v->quantity ?? 0);
        }
        if ($district === self::DISTRICT_URSYNOW) {
            return (int) ($v->quantity_ursynow ?? 0);
        }
        if ($district === self::DISTRICT_PRAGA) {
            return (int) ($v->quantity_praga ?? 0);
        }
        return (int) (($v->quantity_ursynow ?? 0) + ($v->quantity_praga ?? 0));
    }

    /** Загальна кількість: сума по смаках або quantity товару (з урахуванням районів якщо є колонки) */
    public function getTotalQuantityAttribute(): int
    {
        if (Schema::hasColumn('products', 'quantity_ursynow') && Schema::hasColumn('product_variants', 'quantity_ursynow')) {
            return $this->getQuantityForDistrict(null);
        }
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

    /** Ціна на сайті: якщо sale_price задано — вона, інакше purchase_price (для прибутку) */
    public function getWebsitePriceAttribute(): float
    {
        $price = $this->sale_price ?? $this->purchase_price;
        return (float) ($price ?? 0);
    }

    /** Назва для відображення: для категорії Pody додається позначка про рідину в подарунок */
    public function getDisplayNameAttribute(): string
    {
        $translatedName = __($this->name);
        $suffix = ($this->shop_category === 'pods') ? ' (' . __('+ liquid as gift') . ')' : '';
        return $translatedName . $suffix;
    }

    /** Scope: є в наявності для району (Ursynów / Praga) або для InPost (сума обох) */
    public function scopeAvailableInDistrict($query, ?string $district = null)
    {
        if (!Schema::hasColumn('products', 'quantity_ursynow')) {
            return $query->where(function ($q) {
                $q->where('quantity', '>', 0)->orWhereHas('variants', fn ($v) => $v->where('quantity', '>', 0));
            });
        }
        if ($district === self::DISTRICT_URSYNOW) {
            return $query->where(function ($q) {
                $q->where('quantity_ursynow', '>', 0)
                    ->orWhereHas('variants', fn ($v) => $v->where('quantity_ursynow', '>', 0));
            });
        }
        if ($district === self::DISTRICT_PRAGA) {
            return $query->where(function ($q) {
                $q->where('quantity_praga', '>', 0)
                    ->orWhereHas('variants', fn ($v) => $v->where('quantity_praga', '>', 0));
            });
        }
        return $query->where(function ($q) {
            $q->whereRaw('(quantity_ursynow + quantity_praga) > 0')
                ->orWhereHas('variants', fn ($v) => $v->whereRaw('(quantity_ursynow + quantity_praga) > 0'));
        });
    }
}
