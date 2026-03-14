<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
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

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        // Відносний шлях — зображення завжди з того ж хоста/порту, що й сторінка
        return '/storage/' . ltrim($this->image_path, '/');
    }
}
