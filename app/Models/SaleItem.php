<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_variant_id',
        'variant_name',
        'quantity',
        'sale_price',
        'profit',
    ];

    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:2',
            'profit' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /** Назва для відображення: продукт + смак (якщо є) */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->product?->name ?? '—';
        if ($this->variant_name) {
            $name .= ' · ' . $this->variant_name;
        }
        return $name;
    }
}
